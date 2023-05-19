jQuery(document).ready(function($) {

    /* Verticle and Horizontal Tab Start */
    $(".h-tab_content").hide();
    $(".h-tab_container").each(function(){
        $(this).children(":first").show();
    });

    $(".h-tab_tab-head li").click(function () {
        $(".h-tab_content").hide();
        var activeTab = $(this).attr("rel");
        $("#" + activeTab).fadeIn();
        $(this).parent("ul").find("li").removeClass("active");
        $(this).addClass("active");
    });

    $(".v-tab_content").hide();
    $(".v-tab_content:first").show();

    $(".v-tab_tab-head li").click(function () {

        $(".h-tab_content").hide();
        $(".h-tab_container").each(function(){
            $(this).children(":first").show();
        });

        
        $(".v-tab_content").hide();
        var activeTab = $(this).attr("rel");
        $("#" + activeTab).fadeIn();
        $(".v-tab_tab-head li").removeClass("active");
        $(this).addClass("active");
    });
    /* Verticle and Horizontal Tab End*/

    $(".vari-select").change(function(){
        variationID = $(this).val();
        hrefStr = $(".add-to-cart-btn").attr("href");

        // Create a new URL object with the URL string
        const url = new URL(hrefStr);

        // Get the search parameters from the URL
        const searchParams = new URLSearchParams(url.search);

        // Update the value of a specific parameter
        searchParams.set("variation_id", variationID);

        // Update the search property of the URL object with the modified parameters
        url.search = searchParams.toString();

        // Get the modified URL string
        const modifiedUrlString = url.toString();

        $(".add-to-cart-btn").attr("href",modifiedUrlString);
    });
});