<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireAdmin();
$adminPageTitle = 'Products';

$action = sanitize($_GET['action'] ?? 'list');
$msg = ''; $msgType = 'success';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) { $msg = 'Invalid token.'; $msgType = 'danger'; }
    else {
        $act = sanitize($_POST['action'] ?? '');

        if ($act === 'save') {
            $id          = sanitizeInt($_POST['id'] ?? 0);
            $name        = sanitize($_POST['name'] ?? '');
            $catId       = sanitizeInt($_POST['category_id'] ?? 0);
            $price       = (float)($_POST['price'] ?? 0);
            $discPrice   = strlen($_POST['discount_price']??'') ? (float)$_POST['discount_price'] : null;
            $stock       = sanitizeInt($_POST['stock_quantity'] ?? 0);
            $desc        = sanitize($_POST['description'] ?? '');
            $shortDesc   = sanitize($_POST['short_description'] ?? '');
            $sku         = sanitize($_POST['sku'] ?? '');
            $isFeatured  = isset($_POST['is_featured']) ? 1 : 0;
            $isBest      = isset($_POST['is_bestseller']) ? 1 : 0;
            $status      = sanitize($_POST['status'] ?? 'active');
            $metaTitle   = sanitize($_POST['meta_title'] ?? '');
            $metaDesc    = sanitize($_POST['meta_description'] ?? '');
            $slug        = createSlug($name);

            // Handle thumbnail upload
            $thumbnail = $_POST['existing_thumbnail'] ?? '';
            if (!empty($_FILES['thumbnail']['name'])) {
                $up = uploadImage($_FILES['thumbnail'], 'products');
                if ($up) { if ($thumbnail) deleteFile($thumbnail); $thumbnail = $up; }
                else { $msg = 'Image upload failed.'; $msgType = 'danger'; }
            }

            if (!$msg) {
                if ($id) {
                    // Update
                    db()->prepare("UPDATE products SET category_id=?,name=?,slug=?,sku=?,description=?,short_description=?,price=?,discount_price=?,stock_quantity=?,thumbnail=?,is_featured=?,is_bestseller=?,status=?,meta_title=?,meta_description=? WHERE id=?")
                        ->execute([$catId,$name,$slug,$sku,$desc,$shortDesc,$price,$discPrice,$stock,$thumbnail,$isFeatured,$isBest,$status,$metaTitle,$metaDesc,$id]);
                    logActivity('admin',$_SESSION['admin_id'],'product_update',"Updated product #$id");
                    $msg = 'Product updated successfully.';
                } else {
                    // Insert
                    // Ensure unique slug
                    $slugCheck = $slug; $si = 1;
                    while (db()->prepare("SELECT id FROM products WHERE slug=?")->execute([$slugCheck]) && db()->query("SELECT id FROM products WHERE slug='$slugCheck'")->fetch()) {
                        $slugCheck = $slug . '-' . $si++;
                    }
                    db()->prepare("INSERT INTO products (category_id,name,slug,sku,description,short_description,price,discount_price,stock_quantity,thumbnail,is_featured,is_bestseller,status,meta_title,meta_description) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
                        ->execute([$catId,$name,$slugCheck,$sku,$desc,$shortDesc,$price,$discPrice,$stock,$thumbnail,$isFeatured,$isBest,$status,$metaTitle,$metaDesc]);
                    $id = db()->lastInsertId();
                    // Add inventory log
                    if ($stock > 0) db()->prepare("INSERT INTO inventory_logs (product_id,action,quantity_change,quantity_before,quantity_after,note) VALUES (?,?,?,?,?,?)")->execute([$id,'restock',$stock,0,$stock,'Initial stock']);
                    logActivity('admin',$_SESSION['admin_id'],'product_create',"Created product $name");
                    $msg = 'Product created successfully.';
                }

                // Process Variants if submitted
                if (isset($_POST['variants']) && is_array($_POST['variants'])) {
                    $submittedIds = [];
                    foreach ($_POST['variants'] as $vData) {
                        $vId = isset($vData['id']) && $vData['id'] !== '' ? sanitizeInt($vData['id']) : 0;
                        $vName = sanitize($vData['name'] ?? '');
                        $vSku = sanitize($vData['sku'] ?? '');
                        $vPrice = (float)($vData['price'] ?? 0);
                        $vStock = sanitizeInt($vData['stock_quantity'] ?? 0);

                        if (!$vName || !$vSku) continue;

                        if ($vId) {
                            // Update existing variant
                            db()->prepare("UPDATE product_variants SET variant_name = ?, sku = ?, price = ?, stock_quantity = ? WHERE id = ? AND product_id = ?")
                                ->execute([$vName, $vSku, $vPrice, $vStock, $vId, $id]);
                            $submittedIds[] = $vId;
                        } else {
                            // Insert new variant
                            // Verify sku is unique
                            $skuCheck = db()->prepare("SELECT id FROM product_variants WHERE sku = ?");
                            $skuCheck->execute([$vSku]);
                            if ($skuCheck->fetch()) {
                                // Make SKU unique by adding product slug suffix
                                $vSku .= '-' . $id;
                            }
                            db()->prepare("INSERT INTO product_variants (product_id, variant_name, sku, price, stock_quantity) VALUES (?, ?, ?, ?, ?)")
                                ->execute([$id, $vName, $vSku, $vPrice, $vStock]);
                            $submittedIds[] = db()->lastInsertId();
                        }
                    }

                    // Delete variants that were removed by admin
                    if (!empty($submittedIds)) {
                        $inClause = implode(',', array_map('intval', $submittedIds));
                        db()->prepare("DELETE FROM product_variants WHERE product_id = ? AND id NOT IN ($inClause)")
                            ->execute([$id]);
                    } else {
                        db()->prepare("DELETE FROM product_variants WHERE product_id = ?")
                            ->execute([$id]);
                    }
                } else {
                    // No variants submitted, if has_variants_section flag was set, it means all were removed
                    if (isset($_POST['has_variants_section'])) {
                        db()->prepare("DELETE FROM product_variants WHERE product_id = ?")
                            ->execute([$id]);
                    }
                }

                $action = 'list';
            }

        } elseif ($act === 'delete') {
            $id = sanitizeInt($_POST['id'] ?? 0);
            $prod = db()->prepare("SELECT thumbnail FROM products WHERE id=?"); $prod->execute([$id]);
            $p = $prod->fetch();
            if ($p && $p['thumbnail']) deleteFile($p['thumbnail']);
            db()->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
            logActivity('admin',$_SESSION['admin_id'],'product_delete',"Deleted product #$id");
            $msg = 'Product deleted.';

        } elseif ($act === 'toggle_status') {
            $id = sanitizeInt($_POST['id'] ?? 0);
            db()->prepare("UPDATE products SET status = IF(status='active','inactive','active') WHERE id=?")->execute([$id]);
            $msg = 'Status updated.';
        }
    }
}

// List / edit data
$categories = db()->query("SELECT id,name FROM categories WHERE status=1 ORDER BY name")->fetchAll();

if ($action === 'edit') {
    $editId = sanitizeInt($_GET['id'] ?? 0);
    $editProduct = db()->prepare("SELECT * FROM products WHERE id=?"); $editProduct->execute([$editId]); $editProduct = $editProduct->fetch();
    if (!$editProduct) { $action = 'list'; }
    else {
        $vStmt = db()->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY id ASC");
        $vStmt->execute([$editProduct['id']]);
        $editVariants = $vStmt->fetchAll();
    }
}

// List
$search  = sanitize($_GET['search'] ?? '');
$catFilt = sanitizeInt($_GET['cat'] ?? 0);
$where   = []; $params = [];
if ($search) { $where[] = "(p.name LIKE ? OR p.sku LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($catFilt) { $where[] = "p.category_id=?"; $params[] = $catFilt; }
$whereSQL = $where ? 'WHERE ' . implode(' AND ',$where) : '';

$products = db()->prepare("SELECT p.*,c.name AS cat_name FROM products p LEFT JOIN categories c ON p.category_id=c.id $whereSQL ORDER BY p.created_at DESC LIMIT 50");
$products->execute($params);
$products = $products->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Header -->
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <h4>Products</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= APP_URL ?>/admin/dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Products</li></ol></nav>
  </div>
  <a href="<?= APP_URL ?>/admin/products.php?action=new" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Product</a>
</div>

<?php if ($msg): ?>
<div class="alert alert-<?= $msgType ?> alert-dismissible fade show mb-4"><i class="fas fa-<?= $msgType==='success'?'check':'exclamation' ?>-circle me-2"></i><?= htmlspecialchars($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<?php if ($action === 'new' || $action === 'edit'): ?>
<!-- ── Product Form ── -->
<div class="stat-card">
  <h5 style="font-family:var(--font-display);margin-bottom:1.5rem"><?= $action==='edit' ? 'Edit Product' : 'Add New Product' ?></h5>
  <form method="POST" enctype="multipart/form-data">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="save">
    <?php if ($action==='edit'): ?>
    <input type="hidden" name="id" value="<?= $editProduct['id'] ?>">
    <input type="hidden" name="existing_thumbnail" value="<?= htmlspecialchars($editProduct['thumbnail']??'') ?>">
    <?php endif; ?>

    <div class="row g-4">
      <div class="col-lg-8">
        <!-- Basic Info -->
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label fw-bold small">Product Name *</label>
            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($editProduct['name']??'') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold small">Category *</label>
            <select class="form-select" name="category_id" required>
              <option value="">Select Category</option>
              <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= ($editProduct['category_id']??'')==$cat['id']?'selected':'' ?>><?= htmlspecialchars($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold small">SKU *</label>
            <input type="text" class="form-control" name="sku" value="<?= htmlspecialchars($editProduct['sku']??strtoupper(substr(md5(uniqid()),0,8))) ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold small">Price (KSh) *</label>
            <input type="number" class="form-control" name="price" value="<?= $editProduct['price']??'' ?>" min="0" step="0.01" required>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold small">Discount Price</label>
            <input type="number" class="form-control" name="discount_price" value="<?= $editProduct['discount_price']??'' ?>" min="0" step="0.01" placeholder="Leave empty for none">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold small">Stock Quantity</label>
            <input type="number" class="form-control" name="stock_quantity" value="<?= $editProduct['stock_quantity']??0 ?>" min="0">
          </div>
          <div class="col-12">
            <label class="form-label fw-bold small">Short Description</label>
            <input type="text" class="form-control" name="short_description" value="<?= htmlspecialchars($editProduct['short_description']??'') ?>" maxlength="500" placeholder="Brief tagline (max 500 chars)">
          </div>
          <div class="col-12">
            <label class="form-label fw-bold small">Full Description</label>
            <textarea class="form-control" name="description" rows="5"><?= htmlspecialchars($editProduct['description']??'') ?></textarea>
          </div>
          <div class="col-12"><hr style="border-color:var(--border)"><label class="form-label fw-bold small">SEO – Meta Title</label>
            <input type="text" class="form-control" name="meta_title" value="<?= htmlspecialchars($editProduct['meta_title']??'') ?>">
          </div>
          <div class="col-12"><label class="form-label fw-bold small">SEO – Meta Description</label>
            <textarea class="form-control" name="meta_description" rows="2"><?= htmlspecialchars($editProduct['meta_description']??'') ?></textarea>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <!-- Image -->
        <div class="mb-3">
          <label class="form-label fw-bold small">Thumbnail Image</label>
          <?php if (!empty($editProduct['thumbnail'])): ?>
          <div style="border-radius:var(--radius-sm);overflow:hidden;margin-bottom:.75rem;border:1px solid var(--border)">
            <img src="<?= getImageUrl($editProduct['thumbnail']) ?>" id="thumbPreview" style="width:100%;max-height:200px;object-fit:cover">
          </div>
          <?php else: ?>
          <div style="border:2px dashed var(--border);border-radius:var(--radius-sm);padding:2rem;text-align:center;margin-bottom:.75rem">
            <img id="thumbPreview" style="display:none;width:100%;max-height:160px;object-fit:cover;border-radius:6px;margin-bottom:.5rem">
            <i class="fas fa-image" style="font-size:2rem;color:var(--border)"></i>
            <p style="font-size:.8rem;color:var(--text-light);margin:.5rem 0 0">JPG / PNG / WebP – max 5MB</p>
          </div>
          <?php endif; ?>
          <input type="file" class="form-control" name="thumbnail" accept="image/*" data-preview="thumbPreview">
        </div>

        <!-- Options -->
        <div style="background:var(--cream-dark);border-radius:var(--radius-sm);padding:1.25rem">
          <label class="form-label fw-bold small d-block mb-3">Status & Options</label>
          <div class="mb-2">
            <label class="form-label small fw-bold">Status</label>
            <select class="form-select form-select-sm" name="status">
              <?php foreach (['active'=>'Active','inactive'=>'Inactive','out_of_stock'=>'Out of Stock'] as $v=>$l): ?>
              <option value="<?= $v ?>" <?= ($editProduct['status']??'active')===$v?'selected':'' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured" <?= !empty($editProduct['is_featured'])?'checked':'' ?>>
            <label class="form-check-label small" for="isFeatured">⭐ Featured product</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_bestseller" id="isBest" <?= !empty($editProduct['is_bestseller'])?'checked':'' ?>>
            <label class="form-check-label small" for="isBest">🔥 Bestseller</label>
          </div>
        </div>
      </div>
    </div>

    <!-- Product Variants Panel -->
    <div class="row mt-4">
      <div class="col-12">
        <div style="background:var(--cream);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem">
          <input type="hidden" name="has_variants_section" value="1">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 fw-bold" style="font-family:var(--font-display);color:var(--text-dark)"><i class="fas fa-tags me-2" style="color:var(--primary)"></i>Product Variants</h6>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addVariantRow"><i class="fas fa-plus me-1"></i>Add Variant Row</button>
          </div>
          <p class="text-muted small mb-3">Define variant options (e.g. Small, Medium, Large, Special) with unique pricing, SKUs, and stock. If variants exist for a product, customers will be forced to select one before adding it to the cart, and checkout will use the variant's price and stock instead of the main product's fields.</p>
          
          <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle" id="variantsTable">
              <thead>
                <tr class="table-light small">
                  <th style="width:25%">Variant Name (e.g. Medium) *</th>
                  <th style="width:25%">SKU *</th>
                  <th style="width:25%">Price (KSh) *</th>
                  <th style="width:20%">Stock Quantity *</th>
                  <th style="width:5%;text-align:center">Action</th>
                </tr>
              </thead>
              <tbody id="variantsContainer">
                <?php 
                $vIdx = 0;
                if (!empty($editVariants)): 
                  foreach ($editVariants as $v): ?>
                  <tr>
                    <td>
                      <input type="hidden" name="variants[<?= $vIdx ?>][id]" value="<?= $v['id'] ?>">
                      <input type="text" class="form-control form-control-sm" name="variants[<?= $vIdx ?>][name]" value="<?= htmlspecialchars($v['variant_name']) ?>" placeholder="e.g. Medium" required>
                    </td>
                    <td>
                      <input type="text" class="form-control form-control-sm" name="variants[<?= $vIdx ?>][sku]" value="<?= htmlspecialchars($v['sku']) ?>" placeholder="e.g. SKU-MD" required>
                    </td>
                    <td>
                      <input type="number" class="form-control form-control-sm" name="variants[<?= $vIdx ?>][price]" value="<?= $v['price'] ?>" step="0.01" min="0" placeholder="e.g. 500" required>
                    </td>
                    <td>
                      <input type="number" class="form-control form-control-sm" name="variants[<?= $vIdx ?>][stock_quantity]" value="<?= $v['stock_quantity'] ?>" min="0" placeholder="e.g. 10" required>
                    </td>
                    <td class="text-center">
                      <button type="button" class="btn btn-sm btn-link text-danger remove-variant-row p-0"><i class="fas fa-trash"></i></button>
                    </td>
                  </tr>
                  <?php 
                  $vIdx++;
                  endforeach; 
                endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const addBtn = document.getElementById('addVariantRow');
        const container = document.getElementById('variantsContainer');
        let vIdx = <?= $vIdx ?>;

        if (addBtn && container) {
            addBtn.addEventListener('click', function() {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <input type="hidden" name="variants[${vIdx}][id]" value="">
                        <input type="text" class="form-control form-control-sm" name="variants[${vIdx}][name]" placeholder="e.g. Large" required>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="variants[${vIdx}][sku]" placeholder="e.g. SKU-LG" required>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm" name="variants[${vIdx}][price]" step="0.01" min="0" placeholder="e.g. 800" required>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm" name="variants[${vIdx}][stock_quantity]" min="0" placeholder="e.g. 5" required>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-link text-danger remove-variant-row p-0"><i class="fas fa-trash"></i></button>
                    </td>
                `;
                container.appendChild(tr);
                vIdx++;
            });

            container.addEventListener('click', function(e) {
                const removeBtn = e.target.closest('.remove-variant-row');
                if (removeBtn) {
                    const tr = removeBtn.closest('tr');
                    if (tr) tr.remove();
                }
            });
        }
    });
    </script>

    <div class="d-flex gap-3 mt-4">
      <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i><?= $action==='edit'?'Update Product':'Create Product' ?></button>
      <a href="<?= APP_URL ?>/admin/products.php" class="btn btn-outline-primary">Cancel</a>
    </div>
  </form>
</div>

<?php else: ?>
<!-- ── Product List ── -->
<div class="stat-card">
  <!-- Search/filter bar -->
  <form method="GET" class="d-flex flex-wrap gap-2 mb-4">
    <input type="hidden" name="action" value="list">
    <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name or SKU…" style="max-width:260px">
    <select class="form-select" name="cat" style="max-width:180px">
      <option value="">All Categories</option>
      <?php foreach ($categories as $c): ?>
      <option value="<?= $c['id'] ?>" <?= $catFilt==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-primary" type="submit"><i class="fas fa-search me-1"></i>Filter</button>
    <?php if ($search || $catFilt): ?><a href="<?= APP_URL ?>/admin/products.php" class="btn btn-outline-primary">Clear</a><?php endif; ?>
  </form>

  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr>
        <th style="width:70px">Image</th><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Featured</th><th>Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach ($products as $p): ?>
      <tr>
        <td>
          <div style="width:52px;height:52px;border-radius:8px;overflow:hidden;background:var(--cream-dark)">
            <?php if ($p['thumbnail']): ?><img src="<?= getImageUrl($p['thumbnail']) ?>" style="width:100%;height:100%;object-fit:cover"><?php else: ?><div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--border)"><i class="fas fa-birthday-cake"></i></div><?php endif; ?>
          </div>
        </td>
        <td>
          <div style="font-weight:600;font-size:.9rem"><?= htmlspecialchars($p['name']) ?></div>
          <div style="font-size:.75rem;color:var(--text-light)">SKU: <?= htmlspecialchars($p['sku']) ?></div>
        </td>
        <td style="font-size:.875rem"><?= htmlspecialchars($p['cat_name'] ?? '–') ?></td>
        <td>
          <div style="font-weight:700;color:var(--primary)"><?= formatPrice($p['discount_price'] ?? $p['price']) ?></div>
          <?php if ($p['discount_price']): ?><div style="font-size:.75rem;color:var(--text-light);text-decoration:line-through"><?= formatPrice($p['price']) ?></div><?php endif; ?>
        </td>
        <td>
          <span class="badge bg-<?= $p['stock_quantity'] <= 0 ? 'danger' : ($p['stock_quantity'] <= $p['low_stock_threshold'] ? 'warning text-dark' : 'success') ?>">
            <?= $p['stock_quantity'] ?>
          </span>
        </td>
        <td>
          <form method="POST" class="d-inline">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="toggle_status">
            <input type="hidden" name="id" value="<?= $p['id'] ?>">
            <button type="submit" class="badge border-0 bg-<?= $p['status']==='active'?'success':'secondary' ?>" style="cursor:pointer;font-size:.75rem">
              <?= ucfirst($p['status']) ?>
            </button>
          </form>
        </td>
        <td style="text-align:center"><?= $p['is_featured'] ? '⭐' : '–' ?><?= $p['is_bestseller'] ? ' 🔥' : '' ?></td>
        <td>
          <div class="d-flex gap-1">
            <a href="<?= APP_URL ?>/admin/products.php?action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
            <a href="<?= APP_URL ?>/pages/product.php?slug=<?= urlencode($p['slug']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank" title="View"><i class="fas fa-eye"></i></a>
            <form method="POST" class="d-inline">
              <?= csrfField() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $p['id'] ?>">
              <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Delete '<?= htmlspecialchars(addslashes($p['name'])) ?>'? This cannot be undone." title="Delete"><i class="fas fa-trash"></i></button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($products)): ?>
      <tr><td colspan="8" class="text-center py-4" style="color:var(--text-light)">No products found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
