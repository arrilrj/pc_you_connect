<?php
require_once '../includes/auth.php';
 $u = require_login();
 $page_title = 'Report an Item';
 $type = get_str('type') === 'lost' ? 'lost' : 'found';

require_once '../includes/sidebar.php';
require_once '../includes/header.php';
?>
<div class="page-head">
  <div><h1 class="page-title">Report an Item</h1><p class="page-sub">Help reunite items with their owners.</p></div>
  <a class="btn btn-outline-navy" href="lost-found.php">Back to Lost &amp; Found</a>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="card p-4">
      <div class="seg mb-4" id="typeSeg">
        <button type="button" class="seg-btn <?= $type === 'found' ? 'active' : '' ?>" data-type="found"><i class="bi bi-hand-thumbs-up me-1"></i>I Found an Item</button>
        <button type="button" class="seg-btn <?= $type === 'lost' ? 'active' : '' ?>" data-type="lost"><i class="bi bi-search-heart me-1"></i>I Lost an Item</button>
      </div>

      <form method="post" action="../actions/lost-found.php" enctype="multipart/form-data" id="lfForm">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="report_type" id="reportType" value="<?= e($type) ?>">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Item Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="item_name" placeholder="e.g., Silver Watch" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Category</label>
            <select class="form-select" name="category">
              <?php foreach (['electronics'=>'Electronics','id_card'=>'ID / Cards','accessories'=>'Accessories','bags'=>'Bags','keys'=>'Keys','clothing'=>'Clothing','documents'=>'Documents','books'=>'Books','other'=>'Other'] as $k => $v): ?>
                <option value="<?= $k ?>"><?= $v ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label" id="locLabel">Location Found <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="location_found" id="locInput"
                   placeholder="<?= $type === 'lost' ? 'e.g., NAB building hallway' : 'e.g., Found near the gymnasium entrance' ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label" id="dateLabel">Date Found <span class="text-danger">*</span></label>
            <input type="date" class="form-control" name="date_found" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="col-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="3" placeholder="Distinguishing details (optional)"></textarea>
          </div>
          <div class="col-12">
            <label class="form-label">Photo (optional, max 2MB)</label>
            <input type="file" class="form-control" name="image" accept=".jpg,.jpeg,.png,.webp,.gif">
          </div>
          <div class="col-12 d-flex gap-2 flex-wrap">
            <button type="submit" class="btn btn-navy px-4"><i class="bi bi-send me-1"></i>Submit Report</button>
            <a class="btn btn-ghost px-4" href="lost-found.php">Cancel</a>
          </div>
        </div>
      </form>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card p-4 mb-3">
      <h6 class="card-title mb-2" id="hintTitle">Reporting a found item</h6>
      <p class="muted-sm mb-0" id="hintText">Turn over physical items to the Student Affairs Office after reporting. The owner will submit a claim with proof, and the admin verifies it before release.</p>
    </div>
    <div class="card p-4">
      <h6 class="card-title mb-2">Privacy reminder</h6>
      <p class="muted-sm mb-0">Never post personal contact details publicly. Claim verification happens through the admin office.</p>
    </div>
  </div>
</div>

<script>
const HINTS = {
  found: { loc: 'Location Found', date: 'Date Found', ph: 'e.g., Found near the gymnasium entrance',
           title: 'Reporting a found item',
           text: 'Turn over physical items to the Student Affairs Office after reporting. The owner will submit a claim with proof, and the admin verifies it before release.' },
  lost:  { loc: 'Last Seen Location', date: 'Date Lost', ph: 'e.g., Last seen at the NAB library',
           title: 'Reporting a lost item',
           text: 'Describe the item clearly. Admins regularly check new found items — you will get a notification when a possible match is reported.' }
};
let curType = '<?= e($type) ?>';
document.querySelectorAll('#typeSeg .seg-btn').forEach(b => b.addEventListener('click', () => {
  curType = b.dataset.type;
  document.querySelectorAll('#typeSeg .seg-btn').forEach(x => x.classList.toggle('active', x === b));
  document.getElementById('reportType').value = curType;
  const h = HINTS[curType];
  locLabel.innerHTML = h.loc + ' <span class="text-danger">*</span>';
  dateLabel.innerHTML = h.date + ' <span class="text-danger">*</span>';
  locInput.placeholder = h.ph;
  hintTitle.textContent = h.title;
  hintText.textContent = h.text;
}));
</script>

<?php require_once '../includes/footer.php'; ?>