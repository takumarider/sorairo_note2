import "./bootstrap";
import Alpine from "alpinejs";

//jqueryを読み込む
import $ from "jquery";
window.$ = $;
window.jQuery = $;

const defaultReservationLoadingMessage =
    "予約情報を確認しています。画面が切り替わるまでそのままお待ちください。";

if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.start();
}

$(document).ready(function () {
    const $overlay = $("#reservation-loading-overlay");
    const $message = $("#reservation-loading-message");

    const showReservationLoading = (message) => {
        if ($overlay.length === 0) {
            return;
        }

        $message.text(message || defaultReservationLoadingMessage);
        $overlay
            .removeClass("hidden")
            .addClass("flex")
            .attr("aria-hidden", "false");
        $("body").addClass("overflow-hidden");
    };

    const hideReservationLoading = () => {
        if ($overlay.length === 0) {
            return;
        }

        $overlay
            .removeClass("flex")
            .addClass("hidden")
            .attr("aria-hidden", "true");
        $("body").removeClass("overflow-hidden");
    };

    $(document).on("submit", "form[data-loading-overlay='true']", function () {
        const $form = $(this);
        const message = $form.data("loadingMessage");

        showReservationLoading(message);

        window.setTimeout(() => {
            $form
                .find("button[type='submit'], input[type='submit']")
                .prop("disabled", true);
        }, 0);
    });

    $(document).on("click", "a[data-loading-overlay='true']", function () {
        const $link = $(this);
        const message = $link.data("loadingMessage");

        showReservationLoading(message);
        $link
            .addClass("pointer-events-none opacity-70")
            .attr("aria-disabled", "true");
    });

    $(window).on("pageshow", function () {
        hideReservationLoading();
        $("a[data-loading-overlay='true']")
            .removeClass("pointer-events-none opacity-70")
            .removeAttr("aria-disabled");
        $("form[data-loading-overlay='true']")
            .find("button[type='submit'], input[type='submit']")
            .prop("disabled", false);
    });

    $(".test-jquery").on("click", function () {
        $(this).fadeOut(300).fadeIn(300);
        alert("jQuery が正しく動作しています！🎉");
    });
});

// Vite HMR の確認
console.log("✅ Vite is working!");
