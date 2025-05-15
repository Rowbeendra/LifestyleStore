<?php
session_start();
require 'connection.php';

// Get unique values for dropdowns
$categories_query = "SELECT DISTINCT category FROM items ORDER BY category";
$brands_query = "SELECT DISTINCT brand FROM items ORDER BY brand";
$colors_query = "SELECT DISTINCT color FROM items ORDER BY color";
$materials_query = "SELECT DISTINCT material FROM items ORDER BY material";

$categories_result = mysqli_query($con, $categories_query);
$brands_result = mysqli_query($con, $brands_query);
$colors_result = mysqli_query($con, $colors_query);
$materials_result = mysqli_query($con, $materials_query);

// Get min and max prices
$price_query = "SELECT MIN(price) as min_price, MAX(price) as max_price FROM items";
$price_result = mysqli_query($con, $price_query);
$price_range = mysqli_fetch_assoc($price_result);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="shortcut icon" href="img/lifestyleStore.png" />
    <title>Search Products - Lifestyle Store</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" type="text/css">
    <script type="text/javascript" src="bootstrap/js/jquery-3.2.1.min.js"></script>
    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="css/style.css" type="text/css">
    <style>
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .product-card {
            margin-bottom: 20px;
        }
        .price-range {
            width: 100%;
        }
    </style>
</head>
<body>
    <div>
        <?php require 'header.php'; ?>
        
        <div class="container">
            <div class="row">
                <!-- Search Filters -->
                <div class="col-md-3">
                    <div class="filter-section">
                        <h3>Search Filters</h3>
                        <form id="searchForm" method="GET" action="search.php">
                            <div class="form-group">
                                <label>Product Name</label>
                                <input type="text" class="form-control" name="name" value="<?php echo isset($_GET['name']) ? htmlspecialchars($_GET['name']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Category</label>
                                <select class="form-control" name="category">
                                    <option value="">All Categories</option>
                                    <?php while($category = mysqli_fetch_assoc($categories_result)) { ?>
                                        <option value="<?php echo $category['category']; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $category['category']) ? 'selected' : ''; ?>>
                                            <?php echo $category['category']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Brand</label>
                                <select class="form-control" name="brand">
                                    <option value="">All Brands</option>
                                    <?php while($brand = mysqli_fetch_assoc($brands_result)) { ?>
                                        <option value="<?php echo $brand['brand']; ?>" <?php echo (isset($_GET['brand']) && $_GET['brand'] == $brand['brand']) ? 'selected' : ''; ?>>
                                            <?php echo $brand['brand']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Color</label>
                                <select class="form-control" name="color">
                                    <option value="">All Colors</option>
                                    <?php while($color = mysqli_fetch_assoc($colors_result)) { ?>
                                        <option value="<?php echo $color['color']; ?>" <?php echo (isset($_GET['color']) && $_GET['color'] == $color['color']) ? 'selected' : ''; ?>>
                                            <?php echo $color['color']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Material</label>
                                <select class="form-control" name="material">
                                    <option value="">All Materials</option>
                                    <?php while($material = mysqli_fetch_assoc($materials_result)) { ?>
                                        <option value="<?php echo $material['material']; ?>" <?php echo (isset($_GET['material']) && $_GET['material'] == $material['material']) ? 'selected' : ''; ?>>
                                            <?php echo $material['material']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Price Range</label>
                                <div class="row">
                                    <div class="col-xs-6">
                                        <input type="number" class="form-control" name="min_price" placeholder="Min" value="<?php echo isset($_GET['min_price']) ? $_GET['min_price'] : $price_range['min_price']; ?>">
                                    </div>
                                    <div class="col-xs-6">
                                        <input type="number" class="form-control" name="max_price" placeholder="Max" value="<?php echo isset($_GET['max_price']) ? $_GET['max_price'] : $price_range['max_price']; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block">Search</button>
                        </form>
                    </div>
                </div>
                
                <!-- Search Results -->
                <div class="col-md-9">
                    <div id="searchResults">
                        <?php
                        if(isset($_GET['name']) || isset($_GET['category']) || isset($_GET['brand']) || isset($_GET['color']) || isset($_GET['material']) || isset($_GET['min_price']) || isset($_GET['max_price'])) {
                            $where_conditions = array();
                            $params = array();
                            
                            if(!empty($_GET['name'])) {
                                $where_conditions[] = "name LIKE ?";
                                $params[] = "%" . $_GET['name'] . "%";
                            }
                            
                            if(!empty($_GET['category'])) {
                                $where_conditions[] = "category = ?";
                                $params[] = $_GET['category'];
                            }
                            
                            if(!empty($_GET['brand'])) {
                                $where_conditions[] = "brand = ?";
                                $params[] = $_GET['brand'];
                            }
                            
                            if(!empty($_GET['color'])) {
                                $where_conditions[] = "color = ?";
                                $params[] = $_GET['color'];
                            }
                            
                            if(!empty($_GET['material'])) {
                                $where_conditions[] = "material = ?";
                                $params[] = $_GET['material'];
                            }
                            
                            if(!empty($_GET['min_price'])) {
                                $where_conditions[] = "price >= ?";
                                $params[] = $_GET['min_price'];
                            }
                            
                            if(!empty($_GET['max_price'])) {
                                $where_conditions[] = "price <= ?";
                                $params[] = $_GET['max_price'];
                            }
                            
                            $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
                            
                            $query = "SELECT * FROM items $where_clause ORDER BY name";
                            $stmt = mysqli_prepare($con, $query);
                            
                            if(!empty($params)) {
                                $types = str_repeat('s', count($params));
                                mysqli_stmt_bind_param($stmt, $types, ...$params);
                            }
                            
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                            
                            if(mysqli_num_rows($result) > 0) {
                                echo '<div class="row">';
                                while($row = mysqli_fetch_assoc($result)) {
                                    ?>
                                    <div class="col-md-4 product-card">
                                        <div class="thumbnail">
                                            <img src="img/<?php echo strtolower(str_replace(' ', '_', $row['name'])); ?>.jpg" alt="<?php echo $row['name']; ?>">
                                            <div class="caption">
                                                <h3><?php echo $row['name']; ?></h3>
                                                <p>Price: Rs. <?php echo number_format($row['price'], 2); ?></p>
                                                <p><?php echo $row['description']; ?></p>
                                                <?php if(!isset($_SESSION['email'])) { ?>
                                                    <p><a href="login.php" role="button" class="btn btn-primary btn-block">Buy Now</a></p>
                                                <?php } else {
                                                    if(check_if_added_to_cart($row['id'])) {
                                                        echo '<a href="#" class="btn btn-block btn-success disabled">Added to cart</a>';
                                                    } else {
                                                        echo '<a href="cart_add.php?id=' . $row['id'] . '" class="btn btn-block btn-primary">Add to cart</a>';
                                                    }
                                                } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                                echo '</div>';
                            } else {
                                echo '<div class="alert alert-info">No products found matching your criteria.</div>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <br><br><br><br><br><br>
        <footer class="footer">
            <div class="container">
                <center>
                    <p>Copyright &copy Lifestyle Store. All Rights Reserved. | Contact Us: +91 90000 00000</p>
                    <p>This website is developed by Sajal Agrawal</p>
                </center>
            </div>
        </footer>
    </div>
</body>
</html> 