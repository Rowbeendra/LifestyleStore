<?php
    session_start();
    require 'check_if_added.php';
    require 'connection.php';

    // Pagination settings
    $items_per_page = 12;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $items_per_page;

    // Get unique values for dropdowns
    $categories_query = "SELECT DISTINCT category FROM items ORDER BY category";
    $brands_query = "SELECT DISTINCT brand FROM items ORDER BY brand";
    $categories_result = mysqli_query($con, $categories_query);
    $brands_result = mysqli_query($con, $brands_query);

    // Get min and max prices
    $price_query = "SELECT MIN(price) as min_price, MAX(price) as max_price FROM items";
    $price_result = mysqli_query($con, $price_query);
    $price_range = mysqli_fetch_assoc($price_result);
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" href="img/lifestyleStore.png" />
        <title>Lifestyle Store</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- latest compiled and minified CSS -->
        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" type="text/css">
        <!-- jquery library -->
        <script type="text/javascript" src="bootstrap/js/jquery-3.2.1.min.js"></script>
        <!-- Latest compiled and minified javascript -->
        <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
        <!-- External CSS -->
        <link rel="stylesheet" href="css/style.css" type="text/css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Poppins', sans-serif;
                background-color: #f5f5f5;
            }
            .search-section {
                background: #fff;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                margin: 30px 0;
            }
            .search-section .form-control {
                border: 1px solid #e0e0e0;
                padding: 10px 15px;
                height: auto;
                font-size: 14px;
            }
            .search-section .form-control:focus {
                border-color: #2874f0;
                box-shadow: none;
            }
            .products-container {
                padding: 20px 0;
            }
            .product-card {
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                height: 100%;
                display: flex;
                flex-direction: column;
                margin-bottom: 30px;
                overflow: hidden;
            }
            .product-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            }
            .product-image-container {
                position: relative;
                padding-top: 100%;
                overflow: hidden;
                border-radius: 12px 12px 0 0;
                background: #f8f9fa;
            }
            .product-image {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.3s ease;
            }
            .product-card:hover .product-image {
                transform: scale(1.05);
            }
            .product-info {
                padding: 20px;
                flex-grow: 1;
                display: flex;
                flex-direction: column;
                background: #fff;
            }
            .product-title {
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
            .product-price {
                color: #2874f0;
                font-size: 20px;
                font-weight: 600;
                margin: 10px 0;
            }
            .product-price::before {
                content: '₹';
                font-size: 16px;
                margin-right: 2px;
            }
            .btn-view-details {
                background-color: #2874f0;
                border: none;
                padding: 12px;
                font-size: 14px;
                font-weight: 500;
                transition: all 0.3s ease;
                margin-top: auto;
                border-radius: 6px;
            }
            .btn-view-details:hover {
                background-color: #1a5dc8;
                transform: translateY(-2px);
            }
            .pagination {
                margin: 40px 0;
                justify-content: center;
            }
            .pagination .page-link {
                color: #2874f0;
                border: 1px solid #e0e0e0;
                padding: 10px 20px;
                margin: 0 5px;
                border-radius: 6px;
                font-weight: 500;
            }
            .pagination .page-item.active .page-link {
                background-color: #2874f0;
                border-color: #2874f0;
            }
            .pagination .page-link:hover {
                background-color: #f5f5f5;
                color: #1a5dc8;
            }
            .filter-label {
                font-size: 14px;
                font-weight: 500;
                color: #333;
                margin-bottom: 5px;
            }
            .price-input-group {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .price-input-group .form-control {
                flex: 1;
            }
            .price-input-group .separator {
                color: #666;
                font-weight: 500;
            }
            .no-products {
                text-align: center;
                padding: 40px;
                background: #fff;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .no-products i {
                font-size: 48px;
                color: #2874f0;
                margin-bottom: 20px;
            }
            .no-products h3 {
                color: #333;
                font-size: 24px;
                margin-bottom: 10px;
            }
            .no-products p {
                color: #666;
                font-size: 16px;
            }
        </style>
    </head>
    <body>
        <div>
            <?php
                require 'header.php';
            ?>
            <div class="container">
                <div class="jumbotron">
                    <h1>Welcome to our LifeStyle Store!</h1>
                    <p>We have the best cameras, watches and shirts for you. No need to hunt around, we have all in one place.</p>
                </div>
            </div>

            <!-- Search Section -->
            <div class="container">
                <div class="search-section">
                    <form method="GET" action="products.php" class="row g-3">
                        <div class="col-md-4">
                            <label class="filter-label">Search Products</label>
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Search by name or description..." 
                                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="filter-label">Category</label>
                            <select class="form-control" name="category">
                                <option value="">All Categories</option>
                                <?php while($category = mysqli_fetch_assoc($categories_result)) { ?>
                                    <option value="<?php echo $category['category']; ?>" 
                                            <?php echo (isset($_GET['category']) && $_GET['category'] == $category['category']) ? 'selected' : ''; ?>>
                                        <?php echo $category['category']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="filter-label">Brand</label>
                            <select class="form-control" name="brand">
                                <option value="">All Brands</option>
                                <?php while($brand = mysqli_fetch_assoc($brands_result)) { ?>
                                    <option value="<?php echo $brand['brand']; ?>" 
                                            <?php echo (isset($_GET['brand']) && $_GET['brand'] == $brand['brand']) ? 'selected' : ''; ?>>
                                        <?php echo $brand['brand']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="filter-label">Price Range</label>
                            <div class="price-input-group">
                                <input type="number" class="form-control" name="min_price" 
                                       placeholder="Min" value="<?php echo isset($_GET['min_price']) ? $_GET['min_price'] : ''; ?>">
                                <span class="separator">-</span>
                                <input type="number" class="form-control" name="max_price" 
                                       placeholder="Max" value="<?php echo isset($_GET['max_price']) ? $_GET['max_price'] : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <label class="filter-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="container">
                <div class="products-container">
                    <div class="row">
                        <?php
                        // Build search query
                        $where_conditions = array();
                        $params = array();
                        
                        if(!empty($_GET['search'])) {
                            $where_conditions[] = "(name LIKE ? OR description LIKE ?)";
                            $params[] = "%" . $_GET['search'] . "%";
                            $params[] = "%" . $_GET['search'] . "%";
                        }
                        
                        if(!empty($_GET['category'])) {
                            $where_conditions[] = "category = ?";
                            $params[] = $_GET['category'];
                        }
                        
                        if(!empty($_GET['brand'])) {
                            $where_conditions[] = "brand = ?";
                            $params[] = $_GET['brand'];
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
                        
                        // Get total count for pagination
                        $count_query = "SELECT COUNT(*) as total FROM items $where_clause";
                        if(!empty($params)) {
                            $stmt = mysqli_prepare($con, $count_query);
                            $types = str_repeat('s', count($params));
                            mysqli_stmt_bind_param($stmt, $types, ...$params);
                            mysqli_stmt_execute($stmt);
                            $count_result = mysqli_stmt_get_result($stmt);
                        } else {
                            $count_result = mysqli_query($con, $count_query);
                        }
                        $total_items = mysqli_fetch_assoc($count_result)['total'];
                        $total_pages = ceil($total_items / $items_per_page);
                        
                        // Get paginated results
                        $query = "SELECT * FROM items $where_clause ORDER BY name LIMIT ? OFFSET ?";
                        $stmt = mysqli_prepare($con, $query);
                        
                        if(!empty($params)) {
                            $types = str_repeat('s', count($params)) . 'ii';
                            $params[] = $items_per_page;
                            $params[] = $offset;
                            mysqli_stmt_bind_param($stmt, $types, ...$params);
                        } else {
                            mysqli_stmt_bind_param($stmt, 'ii', $items_per_page, $offset);
                        }
                        
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        
                        if(mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                ?>
                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <div class="product-card">
                                        <a href="product_details.php?id=<?php echo $row['id']; ?>" class="product-image-container">
                                            <img src="img/<?php echo strtolower(str_replace(' ', '_', $row['name'])); ?>.jpg" 
                                                 alt="<?php echo $row['name']; ?>"
                                                 class="product-image">
                                        </a>
                                        <div class="product-info">
                                            <h3 class="product-title"><?php echo $row['name']; ?></h3>
                                            <p class="product-price"><?php echo number_format($row['price'], 2); ?></p>
                                            <a href="product_details.php?id=<?php echo $row['id']; ?>" 
                                               class="btn btn-primary btn-block btn-view-details">View Details</a>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            ?>
                            <div class="col-12">
                                <div class="no-products">
                                    <i class="fas fa-search"></i>
                                    <h3>No Products Found</h3>
                                    <p>Try adjusting your search criteria or browse our categories</p>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>

                    <?php if($total_pages > 1) { ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <?php if($page > 1) { ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo isset($_GET['search']) ? '&search='.$_GET['search'] : ''; ?><?php echo isset($_GET['category']) ? '&category='.$_GET['category'] : ''; ?><?php echo isset($_GET['brand']) ? '&brand='.$_GET['brand'] : ''; ?><?php echo isset($_GET['min_price']) ? '&min_price='.$_GET['min_price'] : ''; ?><?php echo isset($_GET['max_price']) ? '&max_price='.$_GET['max_price'] : ''; ?>">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                </li>
                            <?php } ?>
                            
                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            
                            if($start_page > 1) {
                                echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                                if($start_page > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                            }
                            
                            for($i = $start_page; $i <= $end_page; $i++) {
                                ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['search']) ? '&search='.$_GET['search'] : ''; ?><?php echo isset($_GET['category']) ? '&category='.$_GET['category'] : ''; ?><?php echo isset($_GET['brand']) ? '&brand='.$_GET['brand'] : ''; ?><?php echo isset($_GET['min_price']) ? '&min_price='.$_GET['min_price'] : ''; ?><?php echo isset($_GET['max_price']) ? '&max_price='.$_GET['max_price'] : ''; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php
                            }
                            
                            if($end_page < $total_pages) {
                                if($end_page < $total_pages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="?page='.$total_pages.'">'.$total_pages.'</a></li>';
                            }
                            ?>
                            
                            <?php if($page < $total_pages) { ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo isset($_GET['search']) ? '&search='.$_GET['search'] : ''; ?><?php echo isset($_GET['category']) ? '&category='.$_GET['category'] : ''; ?><?php echo isset($_GET['brand']) ? '&brand='.$_GET['brand'] : ''; ?><?php echo isset($_GET['min_price']) ? '&min_price='.$_GET['min_price'] : ''; ?><?php echo isset($_GET['max_price']) ? '&max_price='.$_GET['max_price'] : ''; ?>">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </nav>
                    <?php } ?>
                </div>
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
