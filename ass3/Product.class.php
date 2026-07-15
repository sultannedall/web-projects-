<?php
class Product
{
    private $productId;
    private $productName;
    private $category;
    private $description;
    private $price;
    private $quantity;
    private $rating;
    private $photo1;
    private $photo2;
    private $photo3;
    private $defaultPhoto;

    public function __construct($productId = null, $productName = null, $category = null, $description = null, $price = null, $quantity = null, $rating = null, $photo1 = null, $photo2 = null, $photo3 = null, $defaultPhoto = null)
    {
        $this->productId = $productId;
        $this->productName = $productName;
        $this->category = $category;
        $this->description = $description;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->rating = $rating;
        $this->photo1 = $photo1;
        $this->photo2 = $photo2;
        $this->photo3 = $photo3;
        $this->defaultPhoto = $defaultPhoto;
    }

    // Getters
    public function getProductId() { return $this->productId; }
    public function getProductName() { return $this->productName; }
    public function getCategory() { return $this->category; }
    public function getDescription() { return $this->description; }
    public function getPrice() { return $this->price; }
    public function getQuantity() { return $this->quantity; }
    public function getRating() { return $this->rating; }
    public function getPhoto1() { return $this->photo1; }
    public function getPhoto2() { return $this->photo2; }
    public function getPhoto3() { return $this->photo3; }
    public function getDefaultPhoto() { return $this->defaultPhoto; }

    // Setters
    public function setProductId($v) { $this->productId = $v; }
    public function setProductName($v) { $this->productName = $v; }
    public function setCategory($v) { $this->category = $v; }
    public function setDescription($v) { $this->description = $v; }
    public function setPrice($v) { $this->price = $v; }
    public function setQuantity($v) { $this->quantity = $v; }
    public function setRating($v) { $this->rating = $v; }
    public function setPhoto1($v) { $this->photo1 = $v; }
    public function setPhoto2($v) { $this->photo2 = $v; }
    public function setPhoto3($v) { $this->photo3 = $v; }
    public function setDefaultPhoto($v) { $this->defaultPhoto = $v; }

    private function imagePath($photo)
    {
        $photo = basename((string)$photo);
        $path = 'images/' . $photo;
        if (is_file($path)) return $path;

        $alternate = preg_replace('/\.jpeg$/i', '.jpg', $path);
        if ($alternate && is_file($alternate)) return $alternate;

        return $path;
    }

    private function categoryClass()
    {
        $slug = strtolower(trim((string)$this->category));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        return 'badge-' . ($slug ?: 'general');
    }

    // Render a table row for products listing
    public function displayInTable()
    {
        $img = htmlspecialchars($this->defaultPhoto ?: $this->photo1);
        $pid = (int)$this->productId;
        $name = htmlspecialchars($this->productName);
        $cat = htmlspecialchars($this->category);
        $price = number_format($this->price,2);
        $qty = (int)$this->quantity;

        // Determine actions based on session role
        $actions = '';
        $role = isset($_SESSION['role']) ? $_SESSION['role'] : 'Guest';

        // View button always
        $actions .= '<a href="view.php?id=' . $pid . '">View</a> ';

        if ($role === 'Employee') {
            $actions .= '<a href="edit.php?id=' . $pid . '">Edit</a>';
        } else {
            // Guests and Customers can add to basket
            $actions .= '<a href="add_to_basket.php?id=' . $pid . '">Add to Basket</a>';
        }

        $row  = '<tr>';
        $row .= '<td><img class="table-image" src="images/' . $img . '" alt="' . $name . '"></td>';
        $row .= '<td><a href="view.php?id=' . $pid . '">' . $pid . '</a></td>';
        $row .= '<td>' . $name . '</td>';
        $row .= '<td>' . $cat . '</td>';
        $row .= '<td>' . $price . '</td>';
        $row .= '<td>' . $qty . '</td>';
        $row .= '<td>' . $actions . '</td>';
        $row .= '</tr>';
        return $row;
    }

    public function displayCard()
    {
        $pid = (int)$this->productId;
        $name = htmlspecialchars($this->productName);
        $cat = htmlspecialchars($this->category);
        $price = number_format((float)$this->price, 2);
        $default = $this->defaultPhoto ?: $this->photo1;
        $mainImage = htmlspecialchars($this->imagePath($default));
        $badgeClass = htmlspecialchars($this->categoryClass());
        $photos = [$this->photo1, $this->photo2, $this->photo3];
        $role = isset($_SESSION['role']) ? $_SESSION['role'] : 'Guest';

        $html  = '<article class="product-card">';
        $html .= '<img class="product-image" src="' . $mainImage . '" alt="' . $name . '">';
        $html .= '<h3><a href="view.php?id=' . $pid . '">Product #' . $pid . '</a></h3>';
        $html .= '<p class="product-name">' . $name . '</p>';
        $html .= '<nav class="thumbnail-row" aria-label="Product photos">';
        foreach ($photos as $index => $photo) {
            $photoPath = htmlspecialchars($this->imagePath($photo));
            $active = ($photo === $default) ? ' active-thumb' : '';
            $html .= '<a class="thumbnail-link' . $active . '" href="view.php?id=' . $pid . '">';
            $html .= '<img src="' . $photoPath . '" alt="' . $name . ' photo ' . ($index + 1) . '">';
            $html .= '</a>';
        }
        $html .= '</nav>';
        $html .= '<p><span class="badge ' . $badgeClass . '">' . $cat . '</span></p>';
        $html .= '<p class="product-price">$' . $price . '</p>';
        $html .= '<nav class="card-actions" aria-label="Product actions">';
        $html .= '<a class="button button-neutral" href="view.php?id=' . $pid . '">View</a>';
        if ($role === 'Employee') {
            $html .= '<a class="button button-green" href="edit.php?id=' . $pid . '">Edit</a>';
        } else {
            $html .= '<a class="button button-green" href="add_to_basket.php?id=' . $pid . '">Add to Basket</a>';
        }
        $html .= '</nav>';
        $html .= '</article>';

        return $html;
    }

    // Render full product page (form layout)
    public function displayProductPage()
    {
        $pid = (int)$this->productId;
        $name = htmlspecialchars($this->productName);
        $cat = htmlspecialchars($this->category);
        $desc = htmlspecialchars($this->description);
        $price = htmlspecialchars($this->price);
        $qty = (int)$this->quantity;
        $rating = htmlspecialchars($this->rating);
        $photo1 = htmlspecialchars($this->photo1);
        $photo2 = htmlspecialchars($this->photo2);
        $photo3 = htmlspecialchars($this->photo3);
        $default = htmlspecialchars($this->defaultPhoto);

        $role = isset($_SESSION['role']) ? $_SESSION['role'] : 'Guest';
        $readonly_employee = ($role === 'Employee') ? '' : 'disabled';

        $html  = '<article class="detail-card">';
        $html .= '<h2>Product Details</h2>';
        $html .= '<form class="styled-form" method="post" action="edit.php" enctype="multipart/form-data">';
        $html .= '<input type="hidden" name="product_id" value="' . $pid . '">';
        $html .= '<p class="form-row"><label>Product ID:</label><input type="text" value="' . $pid . '" disabled></p>';
        $html .= '<p class="form-row"><label>Product Name:</label><input type="text" value="' . $name . '" disabled></p>';
        $html .= '<p class="form-row"><label>Category:</label><select disabled><option>' . $cat . '</option></select></p>';
        $html .= '<p class="form-row"><label>Description:</label><textarea name="description" rows="6" ' . $readonly_employee . '>' . $desc . '</textarea></p>';
        $html .= '<p class="form-row"><label>Price:</label><input type="number" step="0.01" name="price" value="' . $price . '" ' . $readonly_employee . '></p>';
        $html .= '<p class="form-row"><label>Quantity:</label><input type="number" name="quantity" value="' . $qty . '" ' . $readonly_employee . '></p>';
        $html .= '<p class="form-row"><label>Rating:</label><input type="text" value="' . $rating . '" disabled></p>';

        // Photos radio buttons
        $html .= '<fieldset><legend>Photos</legend><section class="detail-photos">';
        $html .= '<label class="detail-photo' . ($default === $photo1 ? ' active-thumb' : '') . '"><img src="' . htmlspecialchars($this->imagePath($photo1)) . '" alt="Photo 1"><input type="radio" name="default_photo" value="' . $photo1 . '"' . ($default === $photo1 ? ' checked' : '') . ($role === 'Employee' ? '' : ' disabled') . '> Photo 1</label>';
        $html .= '<label class="detail-photo' . ($default === $photo2 ? ' active-thumb' : '') . '"><img src="' . htmlspecialchars($this->imagePath($photo2)) . '" alt="Photo 2"><input type="radio" name="default_photo" value="' . $photo2 . '"' . ($default === $photo2 ? ' checked' : '') . ($role === 'Employee' ? '' : ' disabled') . '> Photo 2</label>';
        $html .= '<label class="detail-photo' . ($default === $photo3 ? ' active-thumb' : '') . '"><img src="' . htmlspecialchars($this->imagePath($photo3)) . '" alt="Photo 3"><input type="radio" name="default_photo" value="' . $photo3 . '"' . ($default === $photo3 ? ' checked' : '') . ($role === 'Employee' ? '' : ' disabled') . '> Photo 3</label>';
        $html .= '</section>';
        $html .= '</fieldset>';

        $html .= '<nav class="form-actions" aria-label="Product detail actions">';
        if ($role === 'Employee') {
            $html .= '<button class="button button-green" type="submit" name="save">Save Changes</button>';
        } else {
            // Customer or Guest: show add to basket link and back link
            $html .= '<a class="button button-green" href="add_to_basket.php?id=' . $pid . '">Add to Basket</a>';
        }

        $html .= '<a class="button button-neutral" href="products.php">Back to Products</a>';
        $html .= '</nav>';
        $html .= '</form>';
        $html .= '</article>';
        return $html;
    }
}
