<?php
require_once('config.php');
$dbh = new PDO('mysql:host=localhost;dbname=' . $dbname, $user, $passwd);
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
        <title>Selling my stuff</title>
    </head>

    <body>
        <h1 class="text-center"><samp><a href="index.php">Things to sell</a></samp><br /><small>Powered by <a href="https://stripe.com/">Stripe</a></small></h1>
        <div class="container">
            <?php
            $res = $dbh->query("SELECT * FROM items WHERE nb_remaining > 0");
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($item = $res->fetch() )
            {
                    ?>
                    <div class="panel panel-primary">
                        <div class="panel-heading">#<?php echo $item->id; ?> -
                            <?php echo $item->name; ?> -
                            <?php echo $item->price/100;?>€</div>
                        <div class="panel-body">
                            <h2><?php echo $item->name; ?></h2>
                            <div class="row">
                                <div class="col-md-9">
                                Price: <?php echo $item->price/100; ?>€<br/>
                                Description: <?php echo $item->description; ?><br/>
                                Number of items remaining: <?php echo $item->nb_remaining; ?><br/>
                                </div>
                                <?php if ($item->image) { ?>
                                    <div class="col-md-3">
                                        <img class="img-responsive" src="images/<?php echo $item->image; ?>" />
                                    </div>
                                <?php } ?>

                            </div>
                        </div>
                        <div class="panel-footer">
                            <form class="form-inline pull-right" action="order.php" method="POST">
                                <input id="item_id" name="item_id" type="hidden" value="<?php echo $item->id; ?>" />
                                <div class="form-group">
                                  <label for="number">Number of items</label>
                                  <select name="number" class="form-control" id="number">
                                    <?php for ($i = 1; $i <= $item->nb_remaining; $i++) {
                                        echo "<option>" . $i . "</option>";
                                    }?>
                                  </select>
                                </div>
                                <button name="submit" type="submit" class="btn btn-submit" />Order</button>
                            </form>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <?php
            }
            ?>

        </div>
    </body>
</html>
