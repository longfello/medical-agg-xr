<?php if(getenv('ENABLE_GA_TRACKING')=='true'):?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?=getenv('GA_TRACKING_ID')?>"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '<?=getenv('GA_TRACKING_ID')?>');
</script>
<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){ (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o), m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m) })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
    ga('create', '<?=getenv('GA_TRACKING_ID')?>', 'auto', {'allowLinker': true});
    ga('require', 'linker');
    ga('linker:autoLink', ['<?=getenv('PORTAL_HOST')?>','<?=getenv('PROFILE_HOST')?>','<?=getenv('ROUTER_HOST')?>'] );
    ga('send', 'pageview');
</script>
<?php endif;?>

<!--Facebook Pixel JS code. For check use "Facebook Pixel Helper" in Chrome additionals-->
<script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window,document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '193677294698890');
    fbq('track', 'PageView');
</script>
<noscript>
    <img height="1" width="1" src="https://www.facebook.com/tr?id=193677294698890&ev=PageView&noscript=1"/>
</noscript>
