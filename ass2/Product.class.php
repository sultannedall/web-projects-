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
        $row .= '<td><img src="images/' . $img . '" alt="' . $name . '" width="100"></td>';
        $row .= '<td><a href="view.php?id=' . $pid . '">' . $pid . '</a></td>';
        $row .= '<td>' . $name . '</td>';
        $row .= '<td>' . $cat . '</td>';
        $row .= '<td>' . $price . '</td>';
        $row .= '<td>' . $qty . '</td>';
        $row .= '<td>' . $actions . '</td>';
        $row .= '</tr>';
        return $row;
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

        $html  = '<h2>Product Details</h2>';
        $html .= '<form method="post" action="edit.php" enctype="multipart/form-data">';
        $html .= '<input type="hidden" name="product_id" value="' . $pid . '">';
        $html .= '<p><label>Product ID: <input type="text" value="' . $pid . '" disabled></label></p>';
        $html .= '<p><label>Product Name: <input type="text" value="' . $name . '" disabled></label></p>';
        $html .= '<p><label>Category: <select disabled><option>' . $cat . '</option></select></label></p>';
        $html .= '<p><label>Description:<br><textarea name="description" rows="6" cols="60" ' . $readonly_employee . '>' . $desc . '</textarea></label></p>';
        $html .= '<p><label>Price: <input type="number" step="0.01" name="price" value="' . $price . '" ' . $readonly_employee . '></label></p>';
        $html .= '<p><label>Quantity: <input type="number" name="quantity" value="' . $qty . '" ' . $readonly_employee . '></label></p>';
        $html .= '<p><label>Rating: <input type="text" value="' . $rating . '" disabled></label></p>';

        // Photos radio buttons
        $html .= '<fieldset><legend>Photos</legend>';
        $html .= '<p><img src="images/' . $photo1 . '" alt="Photo1" width="120"> <input type="radio" name="default_photo" value="' . $photo1 . '"' . ($default === $photo1 ? ' checked' : '') . ($role === 'Employee' ? '' : ' disabled') . '> Photo 1</p>';
        $html .= '<p><img src="images/' . $photo2 . '" alt="Photo2" width="120"> <input type="radio" name="default_photo" value="' . $photo2 . '"' . ($default === $photo2 ? ' checked' : '') . ($role === 'Employee' ? '' : ' disabled') . '> Photo 2</p>';
        $html .= '<p><img src="images/' . $photo3 . '" alt="Photo3" width="120"> <input type="radio" name="default_photo" value="' . $photo3 . '"' . ($default === $photo3 ? ' checked' : '') . ($role === 'Employee' ? '' : ' disabled') . '> Photo 3</p>';
        $html .= '</fieldset>';

        if ($role === 'Employee') {
            $html .= '<p><button type="submit" name="save">Save Changes</button></p>';
        } else {
            // Customer or Guest: show add to basket link and back link
            $html .= '<p><a href="add_to_basket.php?id=' . $pid . '">Add to Basket</a></p>';
        }

        $html .= '<p><a href="products.php">Back to Products</a></p>';
        $html .= '</form>';
        return $html;
    }
}
