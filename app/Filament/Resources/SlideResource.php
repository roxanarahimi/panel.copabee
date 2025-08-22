<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SlideResource\Pages;
use App\Filament\Resources\SlideResource\RelationManagers;
use App\Models\Slide;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Michaeld555\FilamentCroppie\Components\Croppie;

class SlideResource extends Resource
{
    protected static ?string $model = Slide::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return __('اسلایدها'); // 👈 translation key
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Croppie::make('image')
                    ->label('تصویر اصلی')
                    ->viewportType('rectangle')
                    ->name('content.png')
                    ->required()
                    ->viewportHeight(250)
                    ->viewportWidth(500)
                    ->enableZoom(true)
                    ->disk('public') // or your disk
                    ->directory('img/contents')
                    ->imageFormat('png')
                ,
//                Forms\Components\TextInput::make('link')
//                    ->label('لینک')
//                    ->required()
//                    ->columnSpanFull()
//                    ->maxLength(255),
                Forms\Components\Select::make('active')
                    ->label('نمایش')
                    ->options([
                        '1' => 'بله',
                        '0' => 'خیر',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListSlides::route('/'),
            'create' => Pages\CreateSlide::route('/create'),
            'edit' => Pages\EditSlide::route('/{record}/edit'),
        ];
    }
}
