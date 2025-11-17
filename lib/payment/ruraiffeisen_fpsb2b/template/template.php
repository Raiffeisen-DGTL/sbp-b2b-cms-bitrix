<?php

if (!defined("B_PROLOG_INCLUDED") && !defined("B_PROLOG_DREAMKIT")) {
    die();
}

?>

<style>
    @font-face {
        font-family: 'ALS Hauss Variable';
        src: url('/local/php_interface/include/sale_payment/ruraiffeisen_fpsb2b/template/fonts/ALS Hauss Variable.ttf') format('ttf');
        font-weight: normal;
        font-style: normal;
    }

    #raiffeisen-fps-b2b-button-pay {
        color: #ffffff;
        border: none;
        background-color: #1D1346;
        border-radius: 8px;
        padding: 10px 32px;
        font-size: 16px;
        font-weight: 500;
        display: flex;
        align-items: center;
        font-family: "ALS Hauss Variable", sans-serif;
    }

    #raiffeisen-fps-b2b-button-pay-logo {
        height: 32px;
        margin-left: 12px;
    }
</style>

<script>
    const loadInterval = setTimeout(() => {

        const openForm = () => {
            window.open("<?= $params["QR_URL"]?>", "_blank");
        }
        const button = document.querySelector("#raiffeisen-fps-b2b-button-pay");
        button.addEventListener('click', openForm)
        openForm();
    }, 100)

</script>

<button id="raiffeisen-fps-b2b-button-pay" href="<?= $params["QR_URL"] ?>" target="_blank">
    Оплатить
    <img id="raiffeisen-fps-b2b-button-pay-logo"
         src="/local/php_interface/include/sale_payment/ruraiffeisen_fpsb2b/template/images/logo_fps_b2b.png"
         alt="FPS B2B">
</button>

