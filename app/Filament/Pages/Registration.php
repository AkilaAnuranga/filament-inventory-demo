<?php

namespace App\Filament\Pages;


use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register;

class Registration extends Register
{


    public function form(Form $form): Form
    {
        return $form
            ->schema([


                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255),
                TextInput::make('company_name')
                    ->required(),
                TextInput::make('company_email'),
                TextInput::make('phone'),
                Textarea::make('address'),
                FileUpload::make('logo'),


            ])
            ->statePath('data');



    }


}
