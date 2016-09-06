jQuery(function() {

    function async_load(u,id) {
        if (!gid(id)) {
            s="script", d=document,
            o = d.createElement(s);
            o.type = 'text/javascript';
            o.id = id;
            o.async = true;
            o.src = u;
            // Creating scripts on page
            x = d.getElementsByTagName(s)[0];
            x.parentNode.insertBefore(o,x);
        }
    }

    function gid (id) {
        return document.getElementById(id);
    }

    function getClass (cl) {
        return document.getElementsByClassName(cl);
    }

    if (getClass('s-facebook').length !== 0) {

        var fbs = getClass('s-facebook');
        for (var i = 0; i < fbs.length; i++) {
            fbs[i].setAttribute("data-layout", "button_count");
            fbs[i].setAttribute("data-action", "like");
            fbs[i].setAttribute("data-share", "false");
        }


        var tw = getClass('s-twitter');
        for (var i = 0; i < tw.length; i++) {
            tw[i].setAttribute("data-lang", "ru");
        }


        async_load("//connect.facebook.net/ru_RU/sdk.js#xfbml=1&version=v2.0", "id-facebook");
        async_load("//platform.twitter.com/widgets.js", "id-twitter");
        async_load("//vk.com/js/api/openapi.js", "id-vkontakte");


        var vkID;
        window.location.href.indexOf("server.garin.su") > -1 ? vkID = 4554131 : vkID = 4554148;

        window.vkAsyncInit = function() {
            VK.init({apiId: vkID, onlyWidgets: true});
            VK.Widgets.Like("vk_like", {type: "mini", height: 20});
        };

    }
}); 