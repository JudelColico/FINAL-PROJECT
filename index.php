<?php

include 'config.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

if(isset($_POST['register'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $cpass = sha1($_POST['cpass'] );
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

   $select_user = $conn->prepare("SELECT * FROM `user` WHERE name = ? AND email = ?");
   $select_user->execute([$name, $email]);

   if($select_user->rowCount() > 0){
      $message[] = 'username or email already exists!';
   }else{
      if($pass != $cpass){
         $message[] = 'confirm password not matched!';
      }else{
         $insert_user = $conn->prepare("INSERT INTO `user`(name, email, password) VALUES(?,?,?)");
         $insert_user->execute([$name, $email, $cpass]);
         $message[] = 'registered successfully, login now please!';
      }
   }

}

if(isset($_POST['update_qty'])){
   $cart_id = $_POST['cart_id'];
   $qty = $_POST['qty'];
   $qty = filter_var($qty, FILTER_SANITIZE_STRING);
   $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
   $update_qty->execute([$qty, $cart_id]);
   $message[] = 'cart quantity updated!';
}

if(isset($_GET['delete_cart_item'])){
   $delete_cart_id = $_GET['delete_cart_item'];
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
   $delete_cart_item->execute([$delete_cart_id]);
   header('location:index.php');
}

if(isset($_GET['logout'])){
   session_unset();
   session_destroy();
   header('location:index.php');
}

if(isset($_POST['add_to_cart'])){

   if($user_id == ''){
      $message[] = 'please login first!';
   }else{

      $pid = $_POST['pid'];
      $name = $_POST['name'];
      $price = $_POST['price'];
      $image = $_POST['image'];
      $qty = $_POST['qty'];
      $qty = filter_var($qty, FILTER_SANITIZE_STRING);

      $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND name = ?");
      $select_cart->execute([$user_id, $name]);

      if($select_cart->rowCount() > 0){
         $message[] = 'already added to cart';
      }else{
         $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
         $insert_cart->execute([$user_id, $pid, $name, $price, $qty, $image]);
         $message[] = 'added to cart!';
      }

   }

}

if(isset($_POST['order'])){

   if($user_id == ''){
      $message[] = 'please login first!';
   }else{
      $name = $_POST['name'];
      $name = filter_var($name, FILTER_SANITIZE_STRING);
      $number = $_POST['number'];
      $number = filter_var($number, FILTER_SANITIZE_STRING);
      $address = 'flat no.'.$_POST['flat'].', '.$_POST['street'].' - '.$_POST['pin_code'];
      $address = filter_var($address, FILTER_SANITIZE_STRING);
      $method = $_POST['method'];
      $method = filter_var($method, FILTER_SANITIZE_STRING);
      $total_price = $_POST['total_price'];
      $total_products = $_POST['total_products'];

      $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $select_cart->execute([$user_id]);

      if($select_cart->rowCount() > 0){
         $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?)");
         $insert_order->execute([$user_id, $name, $number, $method, $address, $total_products, $total_price]);
         $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
         $delete_cart->execute([$user_id]);
         $message[] = 'order placed successfully!';
      }else{
         $message[] = 'your cart empty!';
      }
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Baker's Street</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php
   if(isset($message)){
      foreach($message as $message){
         echo '
         <div class="message">
            <span>'.$message.'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
?>


<header class="header">

   <section class="flex">

      <a href="#home" class="logo"><span>B</span>aker's Street.</a>

      <nav class="navbar">
         <a href="#home">Home</a>
         <a href="#menu">Menu</a>
         <a href="#order">Order</a>
         <a href="#about">About us</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
         <div id="order-btn" class="fas fa-box"></div>
         <?php
            $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $count_cart_items->execute([$user_id]);
            $total_cart_items = $count_cart_items->rowCount();
         ?>
         <div id="cart-btn" class="fas fa-shopping-cart"><span>(<?= $total_cart_items; ?>)</span></div>
      </div>

   </section>

</header>


<div class="user-account">

   <section>

      <div id="close-account"><span>close</span></div>

      <div class="user">
         <?php
            $select_user = $conn->prepare("SELECT * FROM `user` WHERE id = ?");
            $select_user->execute([$user_id]);
            if($select_user->rowCount() > 0){
               while($fetch_user = $select_user->fetch(PDO::FETCH_ASSOC)){
                  echo '<p>Welcome ! <span>'.$fetch_user['name'].'</span></p>';
                  echo '<a href="index.php?logout" class="btn">logout</a>';
               }
            }else{
               echo '<p><span>you are not logged in now!</span></p>';
            }
         ?>
      </div>

      <div class="display-orders">
         <?php
            $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $select_cart->execute([$user_id]);
            if($select_cart->rowCount() > 0){
               while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
                  echo '<p>'.$fetch_cart['name'].' <span>('.$fetch_cart['price'].' x '.$fetch_cart['quantity'].')</span></p>';
               }
            }else{
               echo '<p><span>Your cart is empty!</span></p>';
            }
         ?>
      </div>

      <div class="flex">

         <form action="user_login.php" method="post">
            <h3>login now</h3>
            <input type="email" name="email" required class="box" placeholder="enter your email" maxlength="50">
            <input type="password" name="pass" required class="box" placeholder="enter your password" maxlength="20">
            <input type="submit" value="login now" name="login" class="btn">
            <a href="admin_login.php" class="btn">Admin Login</a>';
         </form>

         <form action="" method="post">
            <h3>register now</h3>
            <input type="text" name="name" oninput="this.value = this.value.replace(/\s/g, '')" required class="box" placeholder="enter your username" maxlength="20">
            <input type="email" name="email" required class="box" placeholder="enter your email" maxlength="50">
            <input type="password" name="pass" required class="box" placeholder="enter your password" maxlength="20" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="password" name="cpass" required class="box" placeholder="confirm your password" maxlength="20" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="submit" value="register now" name="register" class="btn">
         </form>

      </div>

   </section>

</div>

<div class="my-orders">

   <section>

      <div id="close-orders"><span>close</span></div>

      <h3 class="title"> my orders </h3>

      <?php
         $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ?");
         $select_orders->execute([$user_id]);
         if($select_orders->rowCount() > 0){
            while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){   
      ?>
      <div class="box">
         <p> placed on : <span><?= $fetch_orders['placed_on']; ?></span> </p>
         <p> name : <span><?= $fetch_orders['name']; ?></span> </p>
         <p> number : <span><?= $fetch_orders['number']; ?></span> </p>
         <p> address : <span><?= $fetch_orders['address']; ?></span> </p>
         <p> payment method : <span><?= $fetch_orders['method']; ?></span> </p>
         <p> total_orders : <span><?= $fetch_orders['total_products']; ?></span> </p>
         <p> total price : <span>Php<?= $fetch_orders['total_price']; ?>/-</span> </p>
         <p> payment status : <span style="color:<?php if($fetch_orders['payment_status'] == 'pending'){ echo 'red'; }else{ echo 'green'; }; ?>"><?= $fetch_orders['payment_status']; ?></span> </p>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">nothing ordered yet!</p>';
      }
      ?>

   </section>

</div>

<div class="shopping-cart">

   <section>

      <div id="close-cart"><span>close</span></div>

      <?php
         $grand_total = 0;
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
              $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
              $grand_total += $sub_total; 
      ?>
      <div class="box">
         <a href="index.php?delete_cart_item=<?= $fetch_cart['id']; ?>" class="fas fa-times" onclick="return confirm('delete this cart item?');"></a>
         <img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="">
         <div class="content">
          <p> <?= $fetch_cart['name']; ?> <span>(<?= $fetch_cart['price']; ?> x <?= $fetch_cart['quantity']; ?>)</span></p>
          <form action="" method="post">
             <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
             <input type="number" name="qty" class="qty" min="1" max="99" value="<?= $fetch_cart['quantity']; ?>" onkeypress="if(this.value.length == 2) return false;">
               <button type="submit" class="fas fa-edit" name="update_qty"></button>
          </form>
         </div>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty"><span>your cart is empty!</span></p>';
      }
      ?>

      <div class="cart-total" ><p style="font-size: large;"> Grand total : <span>Php<?= $grand_total; ?>/-</span> </p></div>

      <a href="#order" class="btn">order now</a>

   </section>

</div>

<div class="home-bg">

   <section class="home" id="home">

      <div class="slide-container">

         <div class="slide active">
            <div class="image">
               <img src="images/sesamestreetdonut.png" alt="sesame street themed donut">
            </div>
            <div class="content">
               <h3>Vanilla Sesame Street Donut</h3>
               <div class="fas fa-angle-left" onclick="prev()"></div>
               <div class="fas fa-angle-right" onclick="next()"></div>
            </div>
         </div>

         
         <div class="slide">
            <div class="image">
               <img src="images/elmocookie.png" alt="elmo themed cookie">
            </div>
            <div class="content">
               <h3>Elmo Strawberry Cookie</h3>
               <div class="fas fa-angle-left" onclick="prev()"></div>
               <div class="fas fa-angle-right" onclick="next()"></div>
            </div>
         </div>

         <div class="slide">
            <div class="image">
               <img src="images/BigbirdCupcake.png" alt="Bigbird themed Cupcake">
            </div>
            <div class="content">
               <h3>Bigbird's Lemon Cupcake</h3>
               <div class="fas fa-angle-left" onclick="prev()"></div>
               <div class="fas fa-angle-right" onclick="next()"></div>
            </div>
         </div>

         <div class="slide">
            <div class="image">
               <img src="images/friendship pie.png" alt="sesame street pie">
            </div>
            <div class="content">
               <h3>Sesame Street Friendship Pie</h3>
               <div class="fas fa-angle-left" onclick="prev()"></div>
               <div class="fas fa-angle-right" onclick="next()"></div>
            </div>
         </div>

         
         <div class="slide">
            <div class="image">
               <img src="images/Matcha Croissant.png" alt="kermit Matcha Croissant">
            </div>
            <div class="content">
               <h3>Kermit's Matcha Croissant</h3>
               <div class="fas fa-angle-left" onclick="prev()"></div>
               <div class="fas fa-angle-right" onclick="next()"></div>
            </div>
         </div>
      </div>

   </section>

</div>





<section id="menu" class="menu">

   <h1 class="heading">our menu</h1>

   <div class="box-container">

      <?php
         $select_products = $conn->prepare("SELECT * FROM `products`");
         $select_products->execute();
         if($select_products->rowCount() > 0){
            while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){    
      ?>
      <div class="box">
         <div class="price">Php<?= $fetch_products['price'] ?>/-</div>
         <img src="uploaded_img/<?= $fetch_products['image'] ?>" alt="">
         <div class="name"><?= $fetch_products['name'] ?></div>
         <form action="" method="post">
            <input type="hidden" name="pid" value="<?= $fetch_products['id'] ?>">
            <input type="hidden" name="name" value="<?= $fetch_products['name'] ?>">
            <input type="hidden" name="price" value="<?= $fetch_products['price'] ?>">
            <input type="hidden" name="image" value="<?= $fetch_products['image'] ?>">
            <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
            <input type="submit" class="btn" name="add_to_cart" value="add to cart">
         </form>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">no products added yet!</p>';
      }
      ?>

   </div>

</section>




<section class="order" id="order">

   <h1 class="heading">order now</h1>

   <form action="" method="post">

   <div class="display-orders">

   <?php
         $grand_total = 0;
         $cart_item[] = '';
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
              $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
              $grand_total += $sub_total; 
              $cart_item[] = $fetch_cart['name'].' ( '.$fetch_cart['price'].' x '.$fetch_cart['quantity'].' ) - ';
              $total_products = implode($cart_item);
              echo '<p>'.$fetch_cart['name'].' <span>('.$fetch_cart['price'].' x '.$fetch_cart['quantity'].')</span></p>';
            }
         }else{
            echo '<p class="empty"><span>your cart is empty!</span></p>';
         }
      ?>

   </div>

      <div class="grand-total"> <p style="font-size: large;">grand total : <span>Php<?= $grand_total; ?>/-</span></p></div>

      <input type="hidden" name="total_products" value="<?= $total_products; ?>">
      <input type="hidden" name="total_price" value="<?= $grand_total; ?>">

      <div class="flex">
         <div class="inputBox">
            <span>your name :</span>
            <input type="text" name="name" class="box" required placeholder="enter your name" maxlength="50">
         </div>
         <div class="inputBox">
            <span>your number :</span>
            <input type="number" name="number" class="box" required placeholder="enter your number" min="0" max="9999999999" onkeypress="if(this.value.length == 10) return false;">
         </div>
         <div class="inputBox">
            <span>payment method</span>
            <select name="method" class="box">
               <option value="cash on delivery">cash on delivery</option>
               <option value="credit card">credit card</option>
               <option value="gcash">gcash</option>
               <option value="paymaya">paymaya</option>
            </select>
         </div>
         <div class="inputBox">
            <span>address line 01 :</span>
            <input type="text" name="flat" class="box" required placeholder="e.g. flat no." maxlength="100">
         </div>
         <div class="inputBox">
            <span>address line 02 :</span>
            <input type="text" name="street" class="box" required placeholder="e.g. street name." maxlength="100">
         </div>
         <div class="inputBox">
            <span>Zip code :</span>
            <input type="number" name="pin_code" class="box" required placeholder="e.g. 123456" min="0" max="999999" onkeypress="if(this.value.length == 6) return false;">
         </div>
      </div>

      <input type="submit" value="order now" class="btn" name="order">

   </form>

</section>

<section class="about" id="about">

   <h1 class="heading">Developers</h1>

   <div class="box-container">

      <div class="box">
         <img src="images/gwen.png" alt="gwen picture">
         <h3>Otic, Gwen Hillary</h3>
         <p>Web Developer, Project Leader, Front-end and Back-end Coder</p>
         <a href="https://github.com/GwenHillary22/GwenHillary22" class="btn">Github Account</a>
      </div>

      <div class="box">
         <img src="images/bianca.png" alt="bianca picture">
         <h3>Cueno, Christa Bianca</h3>
         <p>Web Developer, UI/UX designer, and Content Writter</p>
         <a href="https://github.com/BiancakeCueno" class="btn">Github Account</a>
      </div>

      <div class="box">
         <img src="images/angge.png" alt="angeline picture">
         <h3>Perez, Angeline</h3>
         <p>Web Develope, Resource Manager,and Front-end Coder </p>
         <a href="https://github.com/Perez-Angeline" class="btn">Github Account</a>
      </div>

      <div class="box">
         <img src="images/alef.png" alt="alef picture">
         <h3>Loresca, Alef Justin</h3>
         <p>Web Developer, Resource Manager Assistant, and Front-end Coder</p>
         <a href="https://github.com/krobbus" class="btn">Github Account</a>
      </div>
      
      <div class="box">
         <img src="images/judel.png" alt="judel picture">
         <h3>Colico, Judel</h3>
         <p>Web Developer, Resource Assistant, and Content Writter</p>
         <a href="https://github.com/JudelColico" class="btn">Github Account</a>
      </div>

   </div>

</section>


<section class="footer">

   <div class="box-container">

      <div class="box">
         <i class="fa-brands fa-linkedin"></i>
         <h3>Our Linkedin Accounts</h3>
         <a href="https://www.linkedin.com/in/gwen-hillary-otic-27a0a832a/"<p>Gwen Hillary Otic</p></a>
         <a href="https://www.linkedin.com/in/christa-bianca-cueno-7a74a7322/"<p>Christa Bianca Cueno</p></a>
         <a href="https://www.linkedin.com/in/angeline-perez-56ab16322/"<p>Angeline Perez</p></a>
         <a href="https://ph.linkedin.com/in/alef-justin-loresca-947b17322"<p>Alef Justin Loresca</p></a>
         <a href="https://www.linkedin.com/in/jc-placides-5786b4322/"<p>Judel Colico</p></a>
      </div>

      <div class="box">
         <i class="fas fa-map-marker-alt"></i>
         <h3>Address</h3>
         <p>Muntinlupa City</p>
      </div>

      <div class="box">
         <i class="fas fa-clock"></i>
         <h3>Opening hours</h3>
         <p>09:00am to 10:00pm</p>
      </div>

      <div class="box">
         <i class="fas fa-envelope"></i>
         <h3>Email address</h3>
         <p class="email">oticgwenhillary_bsit@plmun.edu.ph</p>
         <p class="email">cuenochristabianca_bsit@plmun.edu.ph</p>
         <p class="email">perezangeline_bsit@plmun.edu.ph</p>
         <p class="email">lorescaalefjustin_bsit@plmun.edu.ph</p>
         <p class="email">colicojudel_bsit@plmun.edu.ph</p>
      </div>

   </div>

   <div class="credit">
      &copy; copyright @ <?= date('Y'); ?> by <span>INTPROG - BSIT3G - Group1</span> | all rights reserved!
   </div>

</section>

<script src="js/script.js"></script>

</body>
</html>