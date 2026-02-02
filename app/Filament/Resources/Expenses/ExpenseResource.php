<?php

namespace App\Filament\Resources\Expenses;

use App\Filament\Resources\Expenses\Pages\ManageExpenses;
use App\Models\Expense;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $modelLabel = "المصروفات";
    protected static ?string $pluralLabel = "المصروفات";
    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label("العنوان")
                    ->required(),

                TextInput::make('amount')
                    ->label("المبلغ")
                    ->prefix('ILS')
                    ->required()
                    ->numeric(),

                DatePicker::make('date')
                    ->label("تاريخ الصرف")
                    ->required(),

                Textarea::make('notes')
                    ->label("ملاحظات")
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label("العنوان")
                    ->searchable(),

                TextColumn::make('amount')
                    ->label("المبلغ")
                    ->numeric()
                    ->money("ILS", locale: "en")
                    ->sortable()->summarize(
                        Sum::make()
                            ->label('المجموع')
                            ->money('ILS', locale: 'en')
                    ),

                TextColumn::make('date')
                    ->label("تاريخ الصرف")
                    ->date()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('month_year')
                    ->label('فلترة شهرية')
                    ->form([
                        Select::make('month')
                            ->label('الشهر')
                            ->options([
                                '1'  => 'يناير',
                                '2'  => 'فبراير',
                                '3'  => 'مارس',
                                '4'  => 'أبريل',
                                '5'  => 'مايو',
                                '6'  => 'يونيو',
                                '7'  => 'يوليو',
                                '8'  => 'أغسطس',
                                '9'  => 'سبتمبر',
                                '10' => 'أكتوبر',
                                '11' => 'نوفمبر',
                                '12' => 'ديسمبر',
                            ]),
                        Select::make('year')
                            ->label('السنة')
                            ->options(
                                collect(range(now()->year, now()->year - 5))
                                    ->mapWithKeys(fn($year) => [$year => $year])
                                    ->toArray()
                            ),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['month'],
                                fn($q) => $q->whereMonth('created_at', $data['month'])
                            )
                            ->when(
                                $data['year'],
                                fn($q) => $q->whereYear('created_at', $data['year'])
                            );
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageExpenses::route('/'),
        ];
    }
}
