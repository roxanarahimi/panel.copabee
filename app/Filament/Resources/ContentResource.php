<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentResource\Pages;
use App\Filament\Resources\ContentResource\RelationManagers;
use App\Http\Controllers\DateController;
use App\Models\Content;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;
    public static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static function getNavigationLabel(): string
    {
        return __('محتوا'); // 👈 translation key
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('عنوان')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('title_en')
                    ->label('عنوان انگلیسی')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('category_id')
                    ->label('دسته بندی')
                    ->relationship('category', 'title')
                    ->required()
                    ->options(fn (callable $get) =>
                    \App\Models\Category::query()
                        ->when( 1, function ($query) {
                            // Filter categories as needed when conditions are met
                            $query->where('type', 'contents')->where('active', 1);
                        })
                        ->pluck('title', 'id')
                    )
                    ->reactive(), // important so options update when 'type' or 'active' changes

                Forms\Components\Select::make('active')

                    ->label('دیده شود')
                    ->options([
                        '1' => 'بله',
                        '0' => 'خیر',
                        ]),

                RichEditor::make('text')
                    ->label('متن')
                    ->required()
                    ->columnSpanFull()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'strike',
                        'underline',
                        'bulletList',
                        'orderedList',
                        'link',
                        'blockquote',
                        'codeBlock',
                        'file'
                    ]),

            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.title')
            ->label('category'),

                Tables\Columns\TextColumn::make('active'),

                Tables\Columns\TextColumn::make('created_at')
                    ->formatStateUsing(fn ($state) => explode(' ',(new DateController)->toPersian($state))[0]),

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
            'index' => Pages\ListContents::route('/'),
            'create' => Pages\CreateContent::route('/create'),
            'edit' => Pages\EditContent::route('/{record}/edit'),
        ];
    }
}
