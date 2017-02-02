<?php
require_once('../config.php');
$dbh = new PDO('mysql:host=localhost;dbname=' . $dbname, $user, $passwd);
//Please protect this page with .htpasswd
if (isset($_POST['name'])) {

    if (isset($_FILES["image"])) {
        $target_dir = "../images/";
        $filename = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $filename;
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    } else {
        $filename = NULL;
    }

    $insert = $dbh->prepare('INSERT INTO items values (NULL, :name, :image, :description, :number, :price)');
    $insert->execute(array(
        ':name' => $_POST['name'],
        ':image' => $filename,
        ':description' => $_POST['description'],
        ':number' => $_POST['number'],
        ':price' => $_POST['price'],
    ));
}

 ?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Things to sell">
        <meta name="author" content="">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <title>Selling my stuff - Admin</title>
    </head>

    <body>
        <h1 class="text-center"><samp><a href="../index.php">Things to sell</a></samp><br /><small>Powered by <a href="https://stripe.com/">Stripe</a></small></h1>
        <div class="container">
            <?php
            $res = $dbh->query("SELECT * FROM items WHERE nb_remaining > 0");
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($item = $res->fetch() )
            {
                    echo '<div class="panel item">';
                    echo 'Id : '.$item->id.'<br>';
                    echo 'Name : '.$item->name.'<br>';
                    echo 'Price : '.$item->price.'<br>';
                    if ($item->image) {
                         echo '<img class="img-responsive" src="../images/' . $item->image. '" />';
                    }
                    echo 'Description : '.$item->description.'<br>';
                    echo 'Remaining : '.$item->nb_remaining.'<br>';
                    echo '</div>';
            }
            ?>
            <div class="panel panel-primary">
                <div class="panel-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                           <label for="name">Name</label>
                           <input type="text" name="name" class="form-control" id="name">
                         </div>
                         <div class="form-group">
                             <label for="price">Price in cents</label>
                             <input type="number" name="price" class="form-control" id="price">
                         </div>
                         <div class="form-group">
                             <label for="number">Available units</label>
                             <input type="number" name="number" class="form-control" id="number">
                         </div>
                         <div class="form-group">
                           <label for="description">Description</label>
                           <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                         </div>
                         <div>
                         <div class="form-group">
                             <label for="image">Image</label>
                             <input type="file" class="form-control-file" name="image" id="image">
                         </div>
                         <button type="submit" class="btn btn-success pull-right">Create</button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
