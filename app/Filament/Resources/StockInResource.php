<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockInResource\Pages;
use App\Filament\Resources\StockInResource\RelationManagers;
use App\Helpers\TransactionHelper;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockIn;
use App\Models\Transaction;
use App\Models\Vendor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockInResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-information-circle';

    protected static ?string $modelLabel = 'Purchase Order';

    protected static ?string $navigationGroup = 'Sales & Purchase';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([


                Forms\Components\Fieldset::make()
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->label('Invoice NO')
                            ->default(Transaction::where('transaction_type',
                                    'purchase')->latest('number')->get()->max('number') + 1)
                            ->prefix('#')
                            ->disabledOn('edit'),
                        Forms\Components\Select::make('vendor_id')
                            ->options(Vendor::all()->pluck('name', 'id'))
                            ->required()
                            ->default('1')
                            ->label('Vendor'),
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
                                                $get('qty'));
                                            $set('sub_total', $subTotal);
                                            $set('price', TransactionHelper::product_selling_price($product));
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
                                            $subTotal = TransactionHelper::calculate_sub_total($product, $state);
                                            $set('sub_total', $subTotal);
                                            $set('../../grand_total',
                                                TransactionHelper::grandTotal($get('../../products')));
                                        }
                                    }),
                                Forms\Components\TextInput::make('price')
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->required()
                                    ->readOnly(),
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')->label('PO no'),
                Tables\Columns\TextColumn::make('date'),
                Tables\Columns\TextColumn::make('vendor.name')->label('Vendor / Supplier'),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total'),
                Tables\Columns\TextColumn::make('paid'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListStockIns::route('/'),
            'create' => Pages\CreateStockIn::route('/create'),
            'edit' => Pages\EditStockIn::route('/{record}/edit'),
        ];
    }
}
