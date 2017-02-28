<?php
require_once('vendor/autoload.php');
require_once('config.php');

function notify($message) {
  exec('notify "'.  $message . '" > /dev/null 2>/dev/null &');
}

$dbh = new PDO('mysql:host=localhost;dbname=' . $dbname, $user, $passwd);
\Stripe\Stripe::setApiKey($sk);

if (!isset($_POST['item_id']) || !isset($_POST['number'])) {
    header('Location: index.php'); die;
}

$item_id = intval($_POST['item_id']);
$number = intval($_POST['number']);
$query = $dbh->prepare('SELECT * from items WHERE id = :item_id');
$query->execute(array(':item_id' => $item_id));
$query->setFetchMode(PDO::FETCH_OBJ);
$item = $query->fetch();
$item_price = intval($item->price);
$item_nb_remaining = intval($item->nb_remaining);

if ($item_nb_remaining < $number) {
    echo 'Too late. Only ' . $item_nb_remaining . 'left'; die;
}

$total = $number * $item_price;

if (isset($_POST['stripeToken'])) {
    $token = $_POST['stripeToken'];

    try {
      // Use Stripe's library to make requests...
      $charge = \Stripe\Charge::create(array(
        "amount" => $total,
        "currency" => "eur",
        "description" => "Varal7.fr: " . $item->name ,
        "metadata" => array("item_id" => $item_id, "number" => $number),
        "source" => $token,
      ));

    } catch(\Stripe\Error\Card $e) {
      // Since it's a decline, \Stripe\Error\Card will be caught
      $body = $e->getJsonBody();
      $err  = $body['error'];

      print('Status is:' . $e->getHttpStatus() . "\n");
      print('Type is:' . $err['type'] . "\n");
      print('Code is:' . $err['code'] . "\n");
      // param is '' in this case
      print('Param is:' . $err['param'] . "\n");
      print('Message is:' . $err['message'] . "\n");
      die;
    } catch (\Stripe\Error\RateLimit $e) {
      echo "Too many requests. Try again later. Your account has not been debited."; die;
    } catch (\Stripe\Error\InvalidRequest $e) {
      echo "Invalid request. Your account has not been debited. Please contact me for further details."; die;
    } catch (\Stripe\Error\Authentication $e) {
      echo "Invalid authentification. Your account has not been debited. Please contact me for further details."; die;
    } catch (\Stripe\Error\ApiConnection $e) {
      echo "Network error. Try again later. Your account has not been debited."; die;
    } catch (\Stripe\Error\Base $e) {
      echo "Stripe error. Your account has not been debited. Please contact me for further details."; die;
    } catch (Exception $e) {
      echo "Something bad happened. Please contact me for further details."; die;
    }

    $email = $charge->source->name;
    $item_nb_remaining -= $number;

    $update = $dbh->prepare('UPDATE items SET nb_remaining = :nb_remaining WHERE id = :item_id');
    $update->execute(array(':nb_remaining' => $item_nb_remaining, ':item_id' => $item_id));
    $insert = $dbh->prepare('INSERT INTO sold VALUES (NULL, :email, :item_id, :number, NULL)');
    $insert->execute(array(':number' => $number, ':item_id' => $item_id, 'email' => $email));

    $string = $email . ' bought item #' . $item_id . ' (x' . $number . ')';
    notify($string);

    header('Location: success.php'); die;
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Things to sell">
        <meta name="author" content="">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <title>Selling my stuff - Confirmation page</title>
    </head>

    <body>
        <h1 class="text-center"><samp><a href="index.php">Things to sell</a></samp><br /><small>Powered by <a href="https://stripe.com/">Stripe</a></small></h1>
        <div class="container">
            <div>
                <p><a href="index.php">Back to list of items</a></p>
            </div>
            <div class="panel panel-primary text-center">
                <div class="panel-heading">
                    Confirmation box
                </div>
                <?php if ($number ==1) { ?>
                    <h2>You are about to buy: <?php echo $item->name; ?></h2>
                <?php } else { ?>
                    <h2>You are about to buy <?php echo $number; ?> units of: <?php echo $item->name; ?></h2>
                <?php } ?>
                <div class="panel=body">
                    <p><?php echo $item->description; ?></p>
                    <h4>Unit price: <?php echo $item_price/100; ?>€</p>
                    <h4>Ordered: <?php echo $number;?> units</p>
                    <h3>Total: <?php echo $total/100; ?>€</h3>
                    <form class="text-center" action="" method="POST">
                          <input type="hidden" id="item_id" name="item_id" value="<?php echo $item_id; ?>" />
                          <input type="hidden" id="number" name="number" value="<?php echo $number; ?>" />
                          <script
                            src="https://checkout.stripe.com/checkout.js" class="stripe-button"
                            data-key="<?php echo $pk; ?>"
                            data-name="Varal7.fr"
                            data-description="<?php echo $item->name; ?>"
                            data-image="https://stripe.com/img/documentation/checkout/marketplace.png"
                            data-locale="auto"
                            data-amount="<?php echo $total ?>;"
                            data-allow-remember-me="false"
                            data-currency="eur">
                          </script>
                    </form>
                    <br/>
                </div>
            </div>
        </div>
    </body>
</html>
