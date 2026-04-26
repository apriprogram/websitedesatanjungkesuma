<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class HelpCenterController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $recipient = $data['email'];
        $senderEmail = optional($request->user())->email ?? config('mail.from.address');
        $senderName = optional($request->user())->name ?? $data['name'];

        $body = "Pesan baru dari Pusat Bantuan Admin:\n\n"
            . "Nama pengirim: {$data['name']}\n"
            . "Email tujuan: {$recipient}\n"
            . ($senderEmail ? "Email pengirim (login): {$senderEmail}\n" : '')
            . "\nPesan:\n{$data['message']}\n";

        try {
            Mail::raw($body, function ($message) use ($recipient, $data, $senderEmail, $senderName) {
                $message->to($recipient)
                    ->subject('Pesan Pusat Bantuan: ' . $data['name']);

                if ($senderEmail) {
                    $message->replyTo($senderEmail, $senderName);
                }
            });
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['mail' => 'Pesan gagal dikirim: ' . $e->getMessage()]);
        }

        return back()->with('status', 'Pesan berhasil dikirim ke ' . $recipient . '.');
    }
}
