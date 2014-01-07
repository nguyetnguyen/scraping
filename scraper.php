<?php

/*
$a = preg_split('/<div/', '<div class="abc"><a class="test" href="http://google.com.vn">Product Name</a></div>');
foreach($a as $b){
    print $b;   // class="abc"> <a class="test" href="http://google.com.vn">Product Name</a>
}
echo "<p>";

preg_match('/href="(.*?)"/','<div><a class="test" href="http://google.com.vn">Product Name</a></div>',$test1);
var_dump($test1); echo "<p>";   // result: array(2) { [0]=> string(27) "href="http://google.com.vn"" [1]=> string(20) "http://google.com.vn" }

preg_match('/">(.*?)</','<div><a class="test" href="http://google.com.vn">Product Name</a></div>',$test);
var_dump($test); echo "<p>";  //array(2) { [0]=> string(15) "">Product Name<" [1]=> string(12) "Product Name" }

preg_match('/<div>(.*?)<\/div>/','<div><a class="test" href="http://google.com.vn">Product Name</a></div>',$test);
var_dump($test);         // Array(2){
                        //[0]<div><a class="test" href="http://google.com.vn">Product Name</a></div>
                        //[1]=> string(60) "<a class="test" href="http://google.com.vn">Product Name</a>" }
*/

/*
 * Try some preg_match and preg_split
*/

/*$dailybugle = "Spider man Menaces City!";
$regex = "/spider.man/i";
preg_match($regex,$dailybugle,$a);
var_dump($a);*/

//$url = "http://www.nicks.com.au/store";

//$products = getItemProduct($url);
//download_send_headers("data_export_" . date("Y-m-d") . ".csv");
//echo array2csv($products,"text.csv");
?>
<?php
class Scraper{
    private $url = '';
    private $filename = '';

    function __construct($url,$filename){
        $this->url = $url;
        $this->filename = $filename;
    }

    function scrap(){

        $products = $this->getItemProduct();
        $this->array2csv($products);
        $next_url = $this->getContinuePage();
        return json_encode(array('next_url' => htmlspecialchars_decode($next_url),'number_items'=>$this->getRowNumber()));
    }

    function getRowNumber(){
        $row = 0;
        if (($handle = fopen($this->filename, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $num = count($data);
                $row++;
            }
            fclose($handle);
        }
        return $row;
    }

    function getItemProduct(){

        $contents = file_get_contents($this->url);
        $contents = preg_replace('/\s(1,)/', ' ',$contents);

        $products = array();
        $records = preg_split('/<li class="span4/',$contents);
        foreach($records as $item){
            $product = array();

            preg_match('/action="(.*?)"/',$item,$id);
            preg_match('/<h5 class="product-name">(.*?)<\/h5>/',$item,$name);
            preg_match('/<span class="dollars">(.*?)<\/span>/',$item,$dollars);
            preg_match('/<span class="cents">(.*?)<\/span>/',$item,$cent);

            if(isset($id[1])){
                preg_match('{\/product\/(.*?)\/}',$id[1],$p_id);
                if(isset($p_id[1])) $product['id'] = $p_id[1];
            }

            // get name and href
            if(isset($name[1])){
                preg_match('/">(.*?)</',$name[1],$p_name);
                if(isset($p_name[1])) $product['name'] = $p_name[1];

                preg_match('/href="(.*?)"/',$name[1],$href);
                if(isset($href[1])) $product['url'] =$href[1];
            }

            // get regular price
            if(isset($dollars[1]) && isset($cent[1])){
                $p_price = $dollars[1] . $cent[1];
                $product['price'] = $p_price;
            }

            if($product) $products[] = $product;
        }

        return $products;
    }

    function array2csv(array &$array)
    {
        if (count($array) == 0) {
            return null;
        }
        ob_start();
        $is_start = false;
        if(!file_exists($this->filename)) {
            file_put_contents($this->filename,'');
            $is_start = true;
        }
        $df = fopen($this->filename, 'a+');
        if($is_start):
            fputcsv($df, array_keys(reset($array)));
        endif;

        foreach ($array as $row) {
            fputcsv($df, $row);
        }
        fclose($df);
        return ob_get_clean();
    }


    function getContinuePage()
    {
        $contents = file_get_contents($this->url);
        $contents = preg_replace('/\s(1,)/', ' ',$contents);
        $next_url = '';

        $products = array();
        $records = preg_split('/<div class="span3 page-holder/',$contents);

        if(isset($records[1])){
            $lis = preg_split('/<a class="next i-next"/',$records[1]);
            if(isset($lis[1])){
                preg_match('/href="(.*?)"/',$lis[1],$next_page);
                if(isset($next_page[1])){
                    $next_url = $next_page[1];
                }
            }
        }

        return $next_url;
    }

    function PregMatchExpression($expression,$value){
        preg_match($expression,$value,$result);
        return $result;
    }

    function PregSplitContentMain($expression,$content){
        return preg_split($expression,$content);
    }

    function prepareContent($url){
        $content = file_get_contents($url);
        $content = preg_replace('/\s(1,)/', ' ',$content);
        return $content;
    }
}

$scrap = new Scraper($_GET['url'],$_GET['filename']);
$result = $scrap->scrap();
echo $result;
die();