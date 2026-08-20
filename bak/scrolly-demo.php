<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AOS Easing Functions and Anchor Placements</title>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .section {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        .easing-functions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        .easing-functions div {
            width: 200px;
            height: 200px;
            margin: 20px;
            background: #4CAF50;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .anchor-placements {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        .anchor-placements div {
            width: 200px;
            height: 200px;
            margin: 20px;
            background: #4CAF50;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>
    <h1>AOS Easing Functions</h1>
    <div class="easing-functions">
        <div data-aos="fade-up" data-aos-easing="linear">Linear</div>
        <div data-aos="fade-up" data-aos-easing="ease">Ease</div>
        <div data-aos="fade-up" data-aos-easing="ease-in">Ease In</div>
        <div data-aos="fade-up" data-aos-easing="ease-out">Ease Out</div>
        <div data-aos="fade-up" data-aos-easing="ease-in-out">Ease In Out</div>
        <div data-aos="fade-up" data-aos-easing="ease-in-back">Ease In Back</div>
        <div data-aos="fade-up" data-aos-easing="ease-out-back">Ease Out Back</div>
        <div data-aos="fade-up" data-aos-easing="ease-in-out-back">Ease In Out Back</div>
        <div data-aos="fade-up" data-aos-easing="ease-in-sine">Ease In Sine</div>
        <div data-aos="fade-up" data-aos-easing="ease-out-sine">Ease Out Sine</div>
        <div data-aos="fade-up" data-aos-easing="ease-in-out-sine">Ease In Out Sine</div>
    </div>

    <h1>AOS Anchor Placements</h1>
    <div id="anchor-element" style="height: 100px; background: #f0f0f0; margin: 20px;">Anchor Element</div>
    <div class="anchor-placements">
        <div data-aos="fade-up" data-aos-anchor="#anchor-element" data-aos-anchor-placement="top-bottom">Top Bottom</div>
        <div data-aos="fade-up" data-aos-anchor="#anchor-element" data-aos-anchor-placement="top-center">Top Center</div>
        <div data-aos="fade-up" data-aos-anchor="#anchor-element" data-aos-anchor-placement="top-top">Top Top</div>
        <div data-aos="fade-up" data-aos-anchor="#anchor-element" data-aos-anchor-placement="center-bottom">Center Bottom</div>
        <div data-aos="fade-up" data-aos-anchor="#anchor-element" data-aos-anchor-placement="center-center">Center Center</div>
        <div data-aos="fade-up" data-aos-anchor="#anchor-element" data-aos-anchor-placement="center-top">Center Top</div>
        <div data-aos="fade-up" data-aos-anchor="#anchor-element" data-aos-anchor-placement="bottom-bottom">Bottom Bottom</div>
        <div data-aos="fade-up" data-aos-anchor="#anchor-element" data-aos-anchor-placement="bottom-center">Bottom Center</div>
        <div data-aos="fade-up" data-aos-anchor="#anchor-element" data-aos-anchor-placement="bottom-top">Bottom Top</div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
        });
    </script>
</body>
</html>