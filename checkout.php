<?php
require 'db_connection.php';

// Fetch cart items
$stmt = $pdo->query("SELECT cart_items.id, products.name, products.price, cart_items.quantity FROM cart_items JOIN products ON cart_items.product_id = products.id");
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total price
$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += $item['price'] * $item['quantity'];
}

// Initialize Stripe
require 'vendor/autoload.php';
\Stripe\Stripe::setApiKey("Your secret key");

// Create Stripe checkout session
$session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => array_map(function ($item) {
        return [
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => $item['name'],
                ],
                'unit_amount' => (int)($item['price'] * 100),
            ],
            'quantity' => $item['quantity'],
        ];
    }, $cart_items),
    'mode' => 'payment',
    'success_url' => 'http://localhost/shopping_cart/success.php',
    'cancel_url' => 'http://localhost/shopping_cart/cancel.php',
]);
?>

// Generate HTML response
$html = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .checkout-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 400px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        p {
            font-size: 18px;
            margin: 10px 0;
        }
        .button-container {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .pay-button, .cart-button {
            display: inline-block;
            padding: 12px 20px;
            font-size: 18px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .pay-button:hover {
            background-color: #218838;
        }
        .cart-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <h1>Checkout</h1>
        <p><strong>Total:</strong> $'.number_format($total_price, 2).' USD</p>
        <div class="button-container">
            '.(empty($cart_items) ? '' : '<a href="cart.php" class="cart-button">Back to Cart</a>').'
            <a href="'.$session->url.'" class="pay-button" target="_blank">Pay Now</a>
        </div>
    </div>
</body>
</html>
';

// Output the HTML
echo $html;
?>