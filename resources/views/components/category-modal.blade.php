<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="categoryModalTitle">Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="categoryModalForm">
          <input type="hidden" name="id" id="cat-id">
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" id="cat-name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" id="cat-desc" class="form-control"></textarea>
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="cat-active" name="is_active">
            <label class="form-check-label" for="cat-active">Active</label>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" id="categoryModalSave" class="btn btn-primary">Save</button>
      </div>
    </div>
  </div>
</div>
