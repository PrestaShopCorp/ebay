{*
	Rob2014 New File.
*}
<?xml version="1.0" encoding="utf-8"?>
<CompleteSaleRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  <RequesterCredentials>
    <eBayAuthToken>{$ebay_auth_token}</eBayAuthToken>
  </RequesterCredentials>
  <ErrorLanguage>{$error_language}</ErrorLanguage>
  <WarningLevel>High</WarningLevel>
  {if isset($ebayuser) && isset($commentText) && isset($commentType)}
  <FeedbackInfo>
	<CommentText>{$commentText}</CommentText>
    <CommentType>{$commentType}</CommentType>
    <TargetUser>{$ebayuser}</TargetUser>
  </FeedbackInfo>
  {/if}
  {if isset($paid)}<Paid>{$paid}</Paid>{/if}
  {if isset($carrier)}<Shipment>
    <Notes>Shipped {$carrier}</Notes>
  </Shipment>{/if}
  <Shipped>{$shipped}</Shipped>
  <OrderID>{$orderId}</OrderID>
</CompleteSaleRequest>
