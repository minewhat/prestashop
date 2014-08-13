<!-- MineWhat Script-->
<script type='text/javascript'>

  (function() {
    function async_load(){
      if(!window.MWSDK){
          var s = document.createElement('script');
          var toload = 'beaconhttp.minewhat.com';
          s.type = 'text/javascript';
          s.async = true;
          if(location.protocol=='https:')
             toload ='d2ft2mgd1hddln.cloudfront.net';
          s.src = '//' +  toload  + '/site/ethno/{$org}/minewhat.js';
          var x = document.getElementsByTagName('script')[0];
          x.parentNode.insertBefore(s, x);
      }
    }

    if(window.MWSDK && window.MWSDK.reinit)
               window.MWSDK.reinit();

    if (window.attachEvent) window.attachEvent('onload', async_load);
    else window.addEventListener('load', async_load, false);
  })();

</script>
<!-- /MineWhat Script -->
