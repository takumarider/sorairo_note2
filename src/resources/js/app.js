import "./bootstrap";
import Alpine from "alpines";

//jqueryを読み込む
import $ from "jquery";
window.$ = $;
window.jQuery = $;

window.Alpine = Alpine;
Alpine.start();

$(document).ready(function () {
    //テスト用アニメーション
    $("#test-button").on("click", function () {
        $(this).fadeOut(500).fadeIn(500);
    });
});
