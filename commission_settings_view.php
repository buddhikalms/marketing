<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h2 class="h3 text-gray-800">Commission Settings</h2>
            <p class="text-muted">Manage your multi-level marketing commission structure.</p>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="row mb-3">
            <div class="col-12">
                <?php echo $message; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Form Section -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0 rounded-lg">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-plus-circle me-2"></i>Add / Edit Level</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="level" class="form-label fw-bold">Hierarchy Level</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-layer-group"></i></span>
                                <input type="number" class="form-control" id="level" name="level" min="1"
                                    placeholder="e.g. 1" required>
                            </div>
                            <small class="text-muted">1 = Direct Referrals, 2 = 2nd Tier, etc.</small>
                        </div>
                        <div class="mb-3">
                            <label for="rate" class="form-label fw-bold">Commission Rate (%)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-percentage"></i></span>
                                <input type="number" step="0.01" class="form-control" id="rate" name="rate" min="0"
                                    placeholder="e.g. 10.5" required>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="add_level" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Configuration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-lg">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-list me-2"></i>Active Commission
                        Structures</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Level</th>
                                    <th>Commission Rate</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($levels)) : ?>
                                    <?php foreach ($levels as $row) : ?>
                                        <tr>
                                            <td class="ps-4">
                                                <span class="badge bg-info text-dark">Level
                                                    <?php echo htmlspecialchars($row['level']); ?></span>
                                            </td>
                                            <td>
                                                <span
                                                    class="fw-bold text-success"><?php echo htmlspecialchars($row['rate']); ?>%</span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <form method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete Level <?php echo $row['level']; ?>?');"
                                                    class="d-inline">
                                                    <input type="hidden" name="level_id" value="<?php echo $row['id']; ?>">
                                                    <button type="submit" name="delete_level"
                                                        class="btn btn-outline-danger btn-sm" title="Delete Level">
                                                        <i class="fas fa-trash-alt"></i> Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            <i class="fas fa-info-circle me-2"></i>No commission levels defined yet.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>