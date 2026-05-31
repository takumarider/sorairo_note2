import "./bootstrap";

//jqueryを読み込む
import $ from "jquery";
window.$ = $;
window.jQuery = $;

// jQuery の動作確認
$(document).ready(function () {
    console.log("✅ jQuery is loaded and working!");

    // テスト用のアニメーション
    $(".test-jquery").on("click", function () {
        $(this).fadeOut(300).fadeIn(300);
        alert("jQuery が正しく動作しています！🎉");
    });
});

// Vite HMR の確認
console.log("✅ Vite is working!");
