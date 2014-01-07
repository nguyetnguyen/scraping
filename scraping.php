<html>
<head>
    <link rel="stylesheet" type="text/css" href="styles.css" media="all" />
    <script type="text/javascript" src="jquery.js"></script>
</head>
<body>
<div class="page">
    <form action="">
        <div class="input-text">
            <label>Url:</label><input name="url" id="url" value="http://www.nicks.com.au/store/">
            <input type="hidden" name="next_url" id="next_url" value=""/>
        </div>
        <div class="input-text">
            <label>Filename:</label><input name="filename" id="filename" value="abcdef.csv">
        </div>
        <button type="button" id="scrap_url" value="Scrap">Start Scrap</button>
        <div class="running">Please wait for scraping data...</div>
        <div class="result"><a href="#" target="_blank" id="csv_link">See the the result</a></div>
    </form>

<script>
    jQuery.noConflict();
    var url = '';
    var end = false;
    var timer;
    jQuery(document).ready(function(){

        jQuery("#scrap_url").click(function(){
            jQuery(".running").css("display","block");
            jQuery("#scrap_url").css("display","none");
            jQuery("#next_url").val(jQuery("#url").val());
            scrap();
        });

        function scrap(){
            var request = jQuery.ajax({
                                url:"scraper.php",
                                data: {url:jQuery("#next_url").val(),filename:jQuery('#filename').val()}
                            });
            request.done(function(data_response){
                //data  = jQuery.parseJSON(data);
                data = JSON.parse(data_response);

                if(!data.next_url){
                    jQuery(".running").css("display","none");
                    jQuery("#scrap_url").css("display","block");
                    jQuery("#csv_link").attr("href",jQuery('#filename').val());
                }else{
                    jQuery("#next_url").val(data.next_url);
                    jQuery(".running").html(data.number_items + " items will be scraped. Please wait...");
                    scrap();
                }
            });

        }
    });

</script>
</div>
</body>
</html>
