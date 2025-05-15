<?php
session_start();
require 'connection.php';
require 'check_if_added.php';
require 'recommendations.php';

if(!isset($_GET['id'])) {
    header('location: products.php');
    exit();
}

$item_id = $_GET['id'];
$query = "SELECT * FROM items WHERE id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'i', $item_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) == 0) {
    header('location: products.php');
    exit();
}

$item = mysqli_fetch_assoc($result);

// Get recommendations
$recommender = new ProductRecommender($con);
$recommendations = $recommender->getRecommendations($item_id);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="shortcut icon" href="img/lifestyleStore.png" />
    <title><?php echo $item['name']; ?> - Lifestyle Store</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" type="text/css">
    <script type="text/javascript" src="bootstrap/js/jquery-3.2.1.min.js"></script>
    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="css/style.css" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
        }
        .product-details {
            margin: 40px 0;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .product-image-container {
            position: relative;
            padding-top: 100%;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .product-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .product-info {
            padding: 20px;
        }
        .product-title {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
        }
        .product-price {
            font-size: 32px;
            color: #2874f0;
            margin: 20px 0;
            font-weight: 600;
        }
        .product-description {
            margin: 30px 0;
            line-height: 1.8;
            color: #666;
        }
        .product-meta {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #2874f0;
        }
        .product-meta p {
            margin: 15px 0;
            font-size: 16px;
        }
        .product-meta strong {
            color: #2874f0;
            margin-right: 10px;
        }
        .btn-add-cart {
            background-color: #2874f0;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-add-cart:hover {
            background-color: #1a5dc8;
            transform: translateY(-2px);
        }
        .btn-back {
            background-color: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
            padding: 12px 30px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .btn-back:hover {
            background-color: #e9ecef;
            color: #333;
        }
        .btn-disabled {
            background-color: #28a745;
            cursor: not-allowed;
        }
        .recommendations {
            margin-top: 60px;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .recommendations h3 {
            color: #333;
            font-size: 24px;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #2874f0;
        }
        .recommendation-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .recommendation-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .recommendation-image-container {
            position: relative;
            padding-top: 100%;
            overflow: hidden;
            border-radius: 10px 10px 0 0;
        }
        .recommendation-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .recommendation-info {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .recommendation-title {
            font-size: 16px;
            font-weight: 500;
            color: #333;
            margin-bottom: 10px;
            line-height: 1.4;
            height: 44px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .recommendation-price {
            color: #2874f0;
            font-size: 18px;
            font-weight: 600;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div>
        <?php require 'header.php'; ?>
        
        <div class="container">
            <div class="row product-details">
                <div class="col-md-6">
                    <div class="product-image-container">
                        <img src="img/<?php echo strtolower(str_replace(' ', '_', $item['name'])); ?>.jpg" 
                             alt="<?php echo $item['name']; ?>" 
                             class="product-image">
                    </div>
                </div>
                <div class="col-md-6 product-info">
                    <h1 class="product-title"><?php echo $item['name']; ?></h1>
                    <div class="product-price">
                        Rs. <?php echo number_format($item['price'], 2); ?>
                    </div>
                    
                    <div class="product-meta">
                        <p><strong>Category:</strong> <?php echo $item['category']; ?></p>
                        <p><strong>Brand:</strong> <?php echo $item['brand']; ?></p>
                        <p><strong>Color:</strong> <?php echo $item['color']; ?></p>
                        <p><strong>Material:</strong> <?php echo $item['material']; ?></p>
                    </div>
                    
                    <div class="product-description">
                        <h4>Description</h4>
                        <p><?php echo $item['description']; ?></p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <?php if(!isset($_SESSION['email'])) { ?>
                                <a href="login.php" role="button" class="btn btn-primary btn-block btn-add-cart">Login to Buy</a>
                            <?php } else {
                                if(check_if_added_to_cart($item['id'])) {
                                    echo '<a href="#" class="btn btn-block btn-add-cart btn-disabled">Added to cart</a>';
                                } else {
                                    echo '<a href="cart_add.php?id=' . $item['id'] . '" class="btn btn-block btn-add-cart">Add to cart</a>';
                                }
                            } ?>
                        </div>
                        <div class="col-md-6">
                            <a href="products.php" class="btn btn-block btn-back">Back to Products</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if(!empty($recommendations)) { ?>
            <div class="recommendations">
                <h3>You May Also Like</h3>
                <div class="row">
                    <?php foreach($recommendations as $rec) { ?>
                    <div class="col-md-3 mb-4">
                        <div class="recommendation-card">
                            <a href="product_details.php?id=<?php echo $rec['id']; ?>" class="recommendation-image-container">
                                <img src="img/<?php echo strtolower(str_replace(' ', '_', $rec['name'])); ?>.jpg" 
                                     alt="<?php echo $rec['name']; ?>"
                                     class="recommendation-image">
                            </a>
                            <div class="recommendation-info">
                                <h4 class="recommendation-title"><?php echo $rec['name']; ?></h4>
                                <p class="recommendation-price">Rs. <?php echo number_format($rec['price'], 2); ?></p>
                                <a href="product_details.php?id=<?php echo $rec['id']; ?>" 
                                   class="btn btn-primary btn-block btn-view-details">View Details</a>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>
        
        <br><br><br><br><br><br>
        <footer class="footer">
            <div class="container">
                <center>
                    <!-- <p>Copyright &copy Lifestyle Store. All Rights Reserved. | Contact Us: +91 90000 00000</p> -->
                    <p>This website is developed by Salin Maharjan</p>
                </center>
            </div>
        </footer>
    </div>
</body>
</html> 