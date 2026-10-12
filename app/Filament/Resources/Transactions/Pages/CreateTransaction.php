<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Product;
use App\Models\TransactionDetail;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;
    protected function afterCreate(): void
    {
        $transaction = $this->record;

        // Potong stok barang
        foreach ($transaction->transaction_detail as $detail) {
            Product::where('id', $detail->product_id)->decrement('stock', $detail->qty);
        }
    }

    // protected function getRedirectUrl(): string
    // {
    //     // Langsung arahkan ke pencetakan struk setelah tombol Create/Simpan diklik
    //     return route('receipt.print', $this->record->id);
    // }
    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        $url = route('receipt.print', $this->record->id);

        return \Filament\Notifications\Notification::make()
            ->success()
            ->title('Transaksi Berhasil')
            ->body('Transaksi telah disimpan. <a href="' . $url . '" target="_blank" style="text-decoration: underline; font-weight: bold;">Klik di sini untuk cetak ulang struk</a>.')
            ->persistent();
    }
}
