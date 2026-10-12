<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Transaksi')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('invoice_number')
                            ->default('TRX-' . date('YmdHis'))
                            ->label('Transaction Number')
                            ->required()
                            ->readOnly(),

                        Select::make('user_id')
                            ->label('Kasir')
                            ->relationship('user', 'name')
                            ->default(Auth::id())
                            ->required(),
                    ])->columns(2),

                Section::make('Item Belanja')
                    ->columnSpanFull()
                    ->schema([

                        Repeater::make('transaction_detail')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->label('Produk')
                                    ->options(
                                        Product::where('is_active', true)->pluck('name', 'id')
                                    )
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $product = Product::find($state);

                                        if ($product) {
                                            $qty = (float) ($get('qty') ?: 1);
                                            $price = (float) $product->price;

                                            $set('price', $price);
                                            $set('subtotal', $price * $qty);
                                        } else {
                                            $set('price', 0);
                                            $set('subtotal', 0);
                                        }
                                    }),

                                TextInput::make('qty')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(1)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $price = (float) ($get('price') ?? 0);
                                        $qty = (float) ($state ?? 0);

                                        $set('subtotal', $price * $qty);
                                    }),

                                TextInput::make('price')
                                    ->label('Harga Satuan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly(),

                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly(),
                            ])
                            ->columns(4)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateTotals($get, $set);
                            }),

                    ]),

                Section::make('Pembayaran')
                    ->columnSpanFull()
                    ->schema([
                        // Toggle PPN & Dropdown dipindah ke sini
                        Toggle::make('has_ppn')
                            ->label('Kenakan PPN')
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                if (!$get('has_ppn')) {
                                    $set('ppn_rate', null);
                                } else {
                                    $set('ppn_rate', 12); // Default ke 12% saat aktif
                                }
                                self::updateTotals($get, $set);
                            }),

                        Select::make('ppn_rate')
                            ->label('Tarif PPN (%)')
                            ->options([
                                10 => '10%',
                                11 => '11%',
                                12 => '12%',
                            ])
                            ->default(12)
                            ->visible(fn(Get $get) => (bool) $get('has_ppn'))
                            ->required(fn(Get $get) => (bool) $get('has_ppn'))
                            ->live()
                            ->afterStateUpdated(fn(Get $get, Set $set) => self::updateTotals($get, $set)),

                        TextInput::make('tax_amount')
                            ->label('Nominal PPN')
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly(),

                        TextInput::make('total_amount')
                            ->label('Total Harus Dibayar')
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly(),

                        TextInput::make('pay_amount')
                            ->label('Nominal Bayar')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $total = $get('total_amount') ?? 0;
                                $set('change_amount', max(0, (float)$state - (float)$total));
                            }),

                        TextInput::make('change_amount')
                            ->label('Kembalian')
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly(),
                    ])->columns(3), // Menggunakan 3 kolom agar rapi saat dropdown PPN muncul
            ]);
    }

    public static function updateTotals(Get $get, Set $set): void
    {
        $details = $get('transaction_detail') ?? [];
        $subtotal = 0;

        foreach ($details as $item) {
            $subtotal += (float) ($item['subtotal'] ?? 0);
        }

        $hasPpn = $get('has_ppn');
        $ppnRate = (float) ($get('ppn_rate') ?? 0);

        $taxAmount = $hasPpn ? ($subtotal * ($ppnRate / 100)) : 0;
        $totalAmount = $subtotal + $taxAmount;

        $set('tax_amount', $taxAmount);
        $set('total_amount', $totalAmount);

        $payAmount = (float) ($get('pay_amount') ?? 0);
        $set('change_amount', max(0, $payAmount - $totalAmount));
    }
}
