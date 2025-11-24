
<div class="auth-content">

    <h2 class="text-center mb-4 fw-semibold">Prihlásenie</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2"><?= $error ?></div>
    <?php endif; ?>



    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="text" name="email" id="email" class="form-control auth-input" required>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Heslo</label>
        <input type="password" name="password" id="password" class="form-control auth-input" required>
    </div>

    <button type="submit" class="btn btn-primary w-100 auth-btn mt-3">
        Prihlásiť sa
    </button>


</div>



