<?php
/**
 * Class OpenGraph
 *
 */
class OpenGraph{



    /**
     * OGP TwitterCard　を追加するよ
     *
     * @param $out
     * @param  $skin
     *
     * @return bool
     */
    public function addMeta( OutputPage &$out, Skin &$skin ) {
        global $wgRequest,$wgSitename,$wgLogo,$ogpTwitter;

        if ( $wgRequest->getVal( 'action', 'view' ) != 'view' ) {
            return true;
        }

        $meta_t = [];
        $meta_o = [];

        $title = $out->getTitle();
        $page_id  = $title->getArticleID();
        $data = self::getPageData($page_id);

        //Twitter
        $meta_t["twitter:card"]="summary";
        if(isset($ogpTwitter)){
            $meta_t["twitter:site"] = $ogpTwitter;
        }

        //OGP
        $meta_o["og:type"] = "article";
        $meta_o["og:title"] = $title;
        $meta_o["og:url"] = $title->getFullURL();;
        $meta_o["og:site_name"] = $wgSitename;

        //description
        if ( isset($data['extract'])) {
            $description = (string)$data['extract']['*'];
            $meta_o["og:description"] = $description ;
        }

        //image
        if ( isset( $data['thumbnail']['source'])) {
            $image = (string)$data['thumbnail']['source'];
            $meta_o["og:image"]     = $image;
        }else{
            $meta_o["og:image"]     = $wgLogo;
        }

        //Twitter
        foreach ($meta_t as $property => $value) {
            $out->addMeta($property,$value);
        }

        //OGP
        foreach ($meta_o as $property => $value) {
            $out->addHeadItem($property,Html::element( 'meta', array( 'property' => $property, 'content' => $value ) ));
        }

        return true;
    }


    /**
     * 画像と説明を内部API使って取得するよ
     *
     * @param int $page_id
     * @return array|bool
     */
    private static function getPageData($page_id) {
        $prop =[
            'extracts',
            'pageimages',
        ];
        $request = [
            'action' => 'query',
            'prop' => implode( '|', $prop ),
            'exintro' => 'true',
            'exsentences' => '2',
            'explaintext' => 'true',
            'piprop' => 'thumbnail',
            'pithumbsize' => '500',
            'pageids' =>$page_id,
        ];

        //API
        $api = new ApiMain( new FauxRequest( $request ) );
        //API実行
        $api->execute();
        $data = $api->getResult()->getResultData();
        if ( isset( $data['query']['pages'][$page_id]) ) {
            return $data['query']['pages'][$page_id];
        }
        return false;
    }
}