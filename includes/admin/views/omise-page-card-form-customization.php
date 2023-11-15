<?php
  $assetUrl = Omise::get_assets_url();
  $omiseCardGateway = new Omise_Payment_Creditcard();
  $cardFormTheme = $omiseCardGateway->get_option('card_form_theme');
  $cardIcons = $omiseCardGateway->get_card_icons();
  $publicKey = Omise()->settings()->public_key();
  $customFontOther = 'Other';
?>

<link rel="stylesheet" href="<?php echo $assetUrl . '/css/card-form-customization.css'; ?>">

<div class="wrap omise">

  <?php $page->display_messages(); ?>

  <h2><?php echo _e('Omise card form customization', 'omise'); ?></h2>

  <form method="POST">

    <table class="form-table">
      <tr>
        <td class="text-extra-bold" colspan="2">Font</td>
      </tr>

      <tr>
        <td class="text-bold" style="width: 250px;">Font Name</td>
        <td>
          <select id="omise_sf_font_name" class="select-input" name="font[name]">
            <option value="Poppins">Poppins</option>
            <option value="<?php echo $customFontOther ?>"><?php echo $customFontOther ?></option>
          </select>
        </td>
      </tr>

      <tr id="omise_sf_custom_font_name" style="display: none;">
        <td class="text-bold" style="width: 250px;"></td>
        <td>
          <input type="text" class="select-input" placeholder="Font Name" name="font[custom_name]" />
          <div class="description">Specify other font name (note: only Google Fonts supported)</div>
        </td>
      </tr>

      <tr>
        <td class="text-bold" style="width: 250px;">Font Size</td>
        <td>
          <select class="select-input" name="font[size]">
            <option value="16">Large (16px)</option>
            <option value="14">Medium (14px)</option>
            <option value="12">Small (12px)</option>
          </select>
          <div class="description">Custom font size in your form</div>
        </td>
      </tr>

      <tr>
        <td class="text-extra-bold" colspan="2">Form</td>
      </tr>

      <tr>
        <td class="text-bold">Input height</td>
        <td>
          <select class="select-input" name="input[height]">
            <option value="40px">Small (40px)</option>
            <option value="44px">Medium (44px)</option>
            <option value="48px">Large (48px)</option>
            <option value="52px">Extra Large (52px)</option>
          </select>
          <div class="description">Custom your input height</div>
        </td>
      </tr>

      <tr>
        <td class="text-bold">Input border radius</td>
        <td>
          <select class="select-input" name="input[border_radius]">
            <option value="0px">Square</option>
            <option value="4px">Small</option>
            <option value="8px">Medium</option>
            <option value="16px">Large</option>
            <option value="50px">Round</option>
          </select>
          <div class="description">Custom your border radius</div>
        </td>
      </tr>

      <tr>
        <td class="text-bold">Input border color</td>
        <td>
          <div class="color-input-container">
            <input class="color-input" name="input[border_color]" type="color" value="#ced3de">
          </div>
          <div class="description">Select border color to apply with your input</div>
        </td>
      </tr>

      <tr>
        <td class="text-bold">Input active border color</td>
        <td>
          <div class="color-input-container">
            <input class="color-input" name="input[active_border_color]" type="color" value="#1451CC">
          </div>
          <div class="description">Select active border color to apply with your input</div>
        </td>
      </tr>

      <tr>
        <td class="text-bold">Input background color</td>
        <td>
          <div class="color-input-container">
            <input class="color-input" name="input[background_color]" type="color" value="#ffffff">
          </div>
          <div class="description">Select background color to apply with your input</div>
        </td>
      </tr>

      <tr>
        <td class="text-bold">Input label color</td>
        <td>
          <div class="color-input-container">
            <input class="color-input" name="input[label_color]" type="color" value="#212121">
          </div>
          <div class="description">Select label color to apply with your input</div>
        </td>
      </tr>

      <tr>
        <td class="text-bold">Input text color</td>
        <td>
          <div class="color-input-container">
            <input class="color-input" name="input[text_color]" type="color" value="#212121">
          </div>
          <div class="description">Select text color to apply with your input</div>
        </td>
      </tr>

      <tr>
        <td class="text-bold">Input placeholder color</td>
        <td>
          <div class="color-input-container">
            <input class="color-input" name="input[placeholder_color]" type="color" value="#98A1B2">
          </div>
          <div class="description">Select placeholder text color to apply with your input</div>
        </td>
      </tr>

      <tr>
        <td class="text-bold">Checkbox text color</td>
        <td>
          <div class="color-input-container">
            <input class="color-input" name="checkbox[text_color]" type="color" value="#1C2433">
          </div>
          <div class="description">Select your text color to apply with your checkbox</div>
        </td>
      </tr>

      <tr>
        <td class="text-bold">Checkbox theme color</td>
        <td>
          <div class="color-input-container">
            <input class="color-input" name="checkbox[theme_color]" type="color" value="#1451CC">
          </div>
          <div class="description">Select your theme color to apply with your checkbox</div>
        </td>
      </tr>

      <tr>
        <td>
          <input type="submit" name="omise_customization_submit" class="button button-primary" value="Save settings">
          <button id="form-preview" class="button button-preview">Preview</button>
        </td>
        <td>
          <input type="submit" name="omise_customization_reset" class="button button-reset" value="Reset to default settings">
        </td>
      </tr>

    </table>

    <input type="hidden" name="omise_setting_page_nonce" value="<?= wp_create_nonce('omise-setting'); ?>" />
  </form>

  <div class="omise-modal" id="omise-modal">
    <div class="content">
      <div class="body">
        <div id="omise-card" style="width:100%"></div>
      </div>
      <div class="footer">
        <button id="close-form-preview" class="button button-preview">Close Preview</button>
      </div>
    </div>
  </div>
</div>

<script src="<?php echo Omise::OMISE_JS_LINK ?>"></script>
<script>
  window.PUBLIC_KEY = `<?php echo $publicKey ?>`;
  window.CARD_FORM_THEME = `<?php echo $cardFormTheme;  ?>`;
  window.DEFAULT_FORM_DESIGN = JSON.parse(`<?php echo json_encode($formDesign) ?>`);
  window.CARD_BRAND_ICONS = JSON.parse(`<?php echo json_encode($cardIcons) ?>`);
  window.LOCALE = `<?php echo get_locale(); ?>`;
  window.OMISE_CUSTOM_FONT_OTHER = `<?php echo $customFontOther ?>`;
</script>
<script src="<?php echo $assetUrl . '/javascripts/omise-embedded-card.js'; ?>"></script>
<script src="<?php echo $assetUrl . '/javascripts/card-form-customization.js'; ?>"></script>
