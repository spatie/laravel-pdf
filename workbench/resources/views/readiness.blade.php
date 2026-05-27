<div id="target">loading</div>

<script>
    setTimeout(() => {
        document.getElementById('target').innerHTML = 'ready for capture';
        window.pdfReady = true;
    }, 500);
</script>
