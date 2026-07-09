<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Product;

class LowStockAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $p = $this->product;

        return (new MailMessage)
                    ->subject('Low Stock Alert: ' . $p->product_name)
                    ->greeting('Attention,')
                    ->line('The following product has reached or fallen below its minimum stock level:')
                    ->line('Product: ' . $p->product_name)
                    ->line('Category: ' . $p->category)
                    ->line('Current stock: ' . $p->stock)
                    ->line('Minimum stock: ' . $p->minimum_stock)
                    ->action('View Product', url(route('products.edit', $p->id)))
                    ->line('Please review and restock as necessary.');
    }
}
