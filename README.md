

```php
$mcHelper = new MailchimpHelper();
$listid = "pippo";
$cache = new Cache();
$mailchimpResp = $cache->getString("mailchimp-segments", 
    new class MailchimpProvider implements StringProvider
    {
        public function getString() : string
        {
            $response = $mcHelper->get_list_segments($listid);
            return $response["segments"];
        }
    }
);
$mailchimpResp = $cache->getString("mailchimp-segments", 
    function() use($mcHelper, $listid)
    {
        $response = $mcHelper->get_list_segments($listid);
        return $response["segments"];
    }
);
```