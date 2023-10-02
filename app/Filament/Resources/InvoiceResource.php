<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Helpers\TransactionHelper;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use mysql_xdevapi\Schema;

class InvoiceResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $modelLabel = 'Sales Order';

    protected static ?string $navigationGroup = 'Sales & Purchase';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Invoice')
                    ->schema([

                        Forms\Components\Fieldset::make()
                            ->schema([
                                Forms\Components\TextInput::make('number')
                                    ->label('Invoice NO')
                                    ->default(Transaction::where('transaction_type',
                                            'sale')->latest('number')->get()->max('number') + 1)
                                    ->prefix('#')
                                    ->disabledOn('edit'),
                                Forms\Components\Select::make('customer_id')
                                    ->options(Customer::all()->pluck('name', 'id'))
                                    ->required()
                                    ->default('1')
                                    ->label('Customer'),
                                Forms\Components\DatePicker::make('date')
                                    ->default(now()),
                                Forms\Components\TextInput::make('grand_total')
                                    ->readOnly()
                                    ->numeric()
                                    ->default(0)
                                    ->label('Total')

                            ])->columns(4),

                        Forms\Components\Fieldset::make('')
                            ->schema([
                                Forms\Components\Repeater::make('products')
                                    ->relationship('transactionProducts')
                                    ->schema([
                                        Forms\Components\Hidden::make('transaction_id'),
                                        Forms\Components\Select::make('product_id')
                                            ->options(Product::all()->pluck('name', 'id'))
                                            ->searchable()->label('Product')
                                            ->live(onBlur: false)
                                            ->required()
                                            ->afterStateUpdated(function (
                                                Forms\Get $get,
                                                Forms\Set $set,
                                                ?string $state
                                            ) {
                                                if ($state != null) {
                                                    $product = Product::find($state);
                                                    $subTotal = TransactionHelper::calculate_sub_total($product,
                                                        $get('qty'), $get('discount'));
                                                    $set('sub_total', $subTotal);
                                                    $set('price', TransactionHelper::product_selling_price($product));
                                                    $set('df_qty', $product->qty);
                                                    $set('../../grand_total',
                                                        TransactionHelper::grandTotal($get('../../products')));
                                                }
                                            }),
                                        Forms\Components\TextInput::make('qty')
                                            ->numeric()
                                            ->default('1')
                                            ->minValue(1)
                                            ->live(onBlur: true)
                                            ->required()
                                            ->afterStateUpdated(function (
                                                Forms\Get $get,
                                                Forms\Set $set,
                                                ?string $state
                                            ) {
                                                if ($get('product_id') != null) {
                                                    $product = Product::find($get('product_id'));
                                                    $subTotal = TransactionHelper::calculate_sub_total($product, $state,
                                                        $get('discount'));
                                                    $set('sub_total', $subTotal);
                                                    $set('../../grand_total',
                                                        TransactionHelper::grandTotal($get('../../products')));
                                                }
                                            })
                                            ->rules([
                                                function (Forms\Get $get) {
                                                $available_qty = Product::find($get('product_id'))->qty;

                                                return function (string $attribute, $value, \Closure $fail) use (
                                                        $available_qty
                                                    ) {
                                                                if($value > $available_qty){
                                                                    $fail('Only '.$available_qty.' left');
                                                                }
                                                    };
                                                },
                                            ]),
                                        Forms\Components\TextInput::make('price')
                                            ->numeric()
                                            ->inputMode('decimal')
                                            ->required()
                                            ->readOnly(),
                                        Forms\Components\TextInput::make('discount')
                                            ->numeric()
                                            ->default('0')
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->live(onBlur: true)
                                            ->suffix('%')
                                            ->afterStateUpdated(function (
                                                Forms\Get $get,
                                                Forms\Set $set,
                                                ?string $state
                                            ) {
                                                if ($get('product_id') != null) {
                                                    $product = Product::find($get('product_id'));
                                                    $subTotal = TransactionHelper::calculate_sub_total($product,
                                                        $get('qty'), $state);
                                                    $set('sub_total', $subTotal);
                                                    $set('../../grand_total',
                                                        TransactionHelper::grandTotal($get('../../products')));
                                                }
                                            }),
                                        Forms\Components\TextInput::make('sub_total')
                                            ->prefix('$')
                                            ->inputMode('decimal')
                                            ->label('Sub Total')
                                            ->readOnly()
                                            ->numeric(),
                                    ])->deleteAction(function (Forms\Get $get, Forms\Set $set){

                                        $set('grand_total',
                                            TransactionHelper::grandTotal($get('products')));
                                    })
                                    ->required()
                                    ->columns(5),

                            ])
                            ->columns(1),

                        Forms\Components\Fieldset::make('Payment')
                            ->schema([

                                Forms\Components\TextInput::make('paid')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required(),
                                Forms\Components\Select::make('payment_type')
                                    ->options([
                                        'cash' => 'Cash',
                                        'card' => 'Card',
                                        'cheque' => 'Cheque'
                                    ])
                                    ->default('cash')
                                    ->required(),
                                Forms\Components\Textarea::make('remarks')

                            ])->columns(3)

                    ]),

            ])->columns(1);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')->label('Invoice NO'),
                Tables\Columns\TextColumn::make('date'),
                Tables\Columns\TextColumn::make('customer.name')->label('Customer'),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('USD'),
                Tables\Columns\TextColumn::make('paid')
                    ->money('USD'),
                Tables\Columns\TextColumn::make('due')
                    ->state(function ($record) {
                        return $record->grand_total - $record->paid;
                    })
                    ->money('USD')
                    ->badge()
                ->color(function ($state){
                        if($state == 0){
                            return 'success';
                        }
                        return 'danger';

                })


            ])
            ->filters([
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from'),
                        Forms\Components\DatePicker::make('date_to'),
                    ])
                    ->query(function (Builder $query, array $data){
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];


    }


}
