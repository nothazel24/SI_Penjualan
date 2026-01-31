<?php

namespace App\Filament\Resources\ProductTransactionResource\Pages;

use App\Filament\Resources\ProductTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

use Illuminate\Database\Eloquent\Model;
use App\Service\ProductTransactionService;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class EditProductTransaction extends EditRecord
{
    protected static string $resource = ProductTransactionResource::class;

    protected bool $isApprove = false;

    // catch flag / tanda yang dikiirim dan menyimpannya di livewire memory
    public function mount($record): void
    {
        parent::mount($record);

        // dd([
        //     'record_value' => $record,
        //     'record_type' => gettype($record),
        //     'approve_flag' => request()->boolean('approve'),
        //     'session_key' => 'approve_transaction_' . $record,
        //     'session_value' => Session::get('approve_transaction_' . $record, 'NOT FOUND'),
        // ]);

        // simpan session
        if (request()->boolean('approve')) {
            Session::put('approve_transaction_' . $record, true);
        }

        // get session
        $this->isApprove = Session::get('approve_transaction_' . $record, true);
    }

    /*
        calling productTransactionService (update)
        NOTE : method ini meng-override auto-handling validation bawaan filament. jadi gunakan dengan tpat dawg
    */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $isApprove = Session::get('approve_transaction_' . $record->id, false);

        // validasi 
        if ($isApprove) {

            // dd([ // debugging code
            //     'isApprove' => $this->isApprove,
            //     'proof' => $data['proof'],
            //     'blank_proof' => blank($data['proof']),
            // ]);

            if (blank($data['proof'])) { // jika data bukti kosong
                Notification::make()
                    ->title('Transaksi gagal')
                    ->body('Silahkan isi data bukti dengan lengkap!')
                    ->danger()
                    ->send();

                throw new Halt(); // stop process
            }

            // setstate is_paid
            $data['is_paid'] = true;
        }

        try {
            // handle service validasi qty
            $result = app(ProductTransactionService::class)
                ->update($record, $data);

            // notifikasi untuk approve
            if ($isApprove) {
                Notification::make()
                    ->title('Transaksi berhasil')
                    ->body('Transaksi atas nama ' . $record->name . ' berhasil disetujui')
                    ->success()
                    ->send();
            }

            // kembalikan result
            return $result;
        } catch (ValidationException $e) {
            // error qty (service)
            Notification::make()
                ->title('Gagal menyimpan')
                ->body($e->errors()['qty'][0] ?? 'Validasi gagal')
                ->danger()
                ->send();

            throw $e;
        }
    }

    // timpa notifikasi sukses (saat approve saja)
    protected function getSavedNotification(): ?Notification
    {
        $isApprove = Session::get('approve_transaction_' . $this->record->id, false);

        // validasi
        if ($isApprove) {
            return null;
        }
        return parent::getSavedNotification();
    }

    // redirect ke halaman semula
    protected function getRedirectUrl(): ?string
    {
        $isApprove = Session::get('approve_transaction_' . $this->record->id, false);

        // validasi script
        if ($isApprove) {
            // hapus session jika berhasil
            Session::forget('approve_transaction_' . $this->record->id,);

            return ProductTransactionResource::getUrl('index');
        }

        // selalu kembalikan nilai ketika memakai ?string
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            // Force delete (permanent) & restore
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
