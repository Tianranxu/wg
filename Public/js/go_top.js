/**
 * Created by DA mimi on 2016/1/18.
 */

var currentPosition, timer;

/**
 * 返回顶部
 * @constructor
 */
function GoTop() {
    timer = setInterval("runToTop()", 1);
}
function runToTop() {
    currentPosition = document.documentElement.scrollTop || document.body.scrollTop;
    currentPosition -= 10;
    if (currentPosition > 0) {
        window.scrollTo(0, currentPosition);
    }
    else {
        window.scrollTo(0, 0);
        clearInterval(timer);
    }
}

/**
 * 圆滑滚动到指定锚点
 * @param obj class类名或id名称    如：.xxxx或#xxx
 * @param speed 滚动速度，默认500，也可以用slow，normal，fast
 * @returns {boolean}
 */
function goAnchor(obj, speed) {
    if (!speed) speed = 500;
    $('html,body').animate({scrollTop: $(obj).offset().top}, speed);
    return false;
}