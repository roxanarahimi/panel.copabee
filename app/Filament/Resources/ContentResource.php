<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentResource\Pages;
use App\Filament\Resources\ContentResource\RelationManagers;
use App\Http\Controllers\DateController;
use App\Models\Content;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\ImageFile;
use Michaeld555\FilamentCroppie\Components\Croppie;
use Illuminate\Database\Eloquent\Model;


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
//                ImageFile::image(Storage::get('image')),
                Forms\Components\TextInput::make('title')
                    ->label('عنوان')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(255),
//                Forms\Components\TextInput::make('title_en')
//                    ->label('عنوان انگلیسی')
//                    ->required()
//                    ->maxLength(255),
                Forms\Components\Select::make('category_id')
                    ->label('دسته بندی')
                    ->relationship('category', 'title')
                    ->required()
                    ->options(fn(callable $get) => \App\Models\Category::query()
                        ->when(1, function ($query) {
                            // Filter categories as needed when conditions are met
                            $query->where('type', 'contents')->where('active', 1);
                        })
                        ->pluck('title', 'id')
                    )
                    ->reactive(), // important so options update when 'type' or 'active' changes

                Forms\Components\Select::make('active')
                    ->label('نمایش')
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
//                Tables\Columns\ImageColumn::make('image')->circular(),
                Tables\Columns\ImageColumn::make('image')
                    ->getStateUsing(function ($record): string {
                        return $record->image;
                    }),
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.title')
                    ->label('دسته بندی'),


                Tables\Columns\IconColumn::make('active')
                    ->label('نمایش')
                    ->boolean()
                    ->trueIcon('heroicon-o-check')->trueColor('success')
                    ->falseIcon('heroicon-o-x-mark')->falseColor('danger')
                ,
//                    ->formatStateUsing(fn ($state) => $state ? 'بله' : 'خیر'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->formatStateUsing(fn($state) => explode(' ', (new DateController)->toPersian($state))[0]),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('دسته بندی')
                    ->options(
                        fn () => \App\Models\Category::query()
                            ->where('type', 'contents')
                            ->where('active', 1)
                            ->pluck('title', 'id')
                    ),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
