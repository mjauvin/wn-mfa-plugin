<h2>Provide your 2FA Authorization code below</h2>

<?= Form::open() ?>
    <input type="hidden" name="postback" value="1" />

    <div class="form-elements" role="form">
        <div class="form-group text-field horizontal-form">

            <!-- Login -->
            <input
                type="text"
                name="authcode"
                class="form-control"
                placeholder="Authentication Code"
                autocomplete="off"
                maxlength="8" />

            <!-- Submit Login -->
            <button type="submit" class="btn btn-primary login-button">
                verify
            </button>
        </div>
    </div>
<?= Form::close() ?>

<?= $this->fireViewEvent('backend.auth.extendAuthcodeView') ?>
