<!DOCTYPE html>
<html>
<head>
    <title>Hangman Game</title>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hangman</title>
    <!-- Google Fonts -->
    <link rel="shortcut icon" href="{{asset('images/favicon.png')}}">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Montserrat:wght@400;500&display=swap" rel="stylesheet">
   <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&amp;display=swap" rel="stylesheet">
  
    <style>
     body {
         font-family: 'Poppins', Helvetica, Arial, Lucida, sans-serif !important;
            /*font-family: 'Montserrat', sans-serif;*/
            line-height: 1.6;
            color: #2c3e50;
            background-color: #fdfdfd;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            text-align: center;
            padding: 20px;
        }
        .win_img{
            position: absolute;
    top: 50px;
    right: 20px;
    width: 30%;
    height: 65%;
        }
          .lose_img{
            position: absolute;
    top: 50px;
    right: 20px;
    width: 30%;
    height: 65%;
        }
      #display_correct_word{
              color: #e33131;
    font-size: 20px;
    font-family:  cursive!important;
        /*font-family: Arial!important;*/

      }
      #keyboard{
               padding-left: 60px;
    padding-right: 60px;
    display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 5px;
            width: 100%;
            max-width: 600px; /* Adjust based on screen size */
            margin: auto;
            padding: 10px;
      }
.letter-btn{
     display: flex;
            align-items: center;
            justify-content: center;
            width: calc(100% / 10 - 5px); /* Adjust grid size dynamically */
            max-width: 50px;
            height: 50px;
            font-size: 1.5rem;
            font-weight: bold;
            /*text-transform: uppercase;*/
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: 1px 1px 1px 1px #58595a33;
}
.letter-btn:hover{
    box-shadow:none;
}
 .letter-btn:disabled,
 .letter-btn[disabled] {
        background-color: red !important;
    color: white!important;
    box-shadow: none;

}
#word-display{
    font-size: 33px;
    color: #b88942;
    display:inline-flex;
}
.hidden_word{
  height:7px;
  padding:2px;
  background-color:#608d9d;
  width:25px;
  border:1px solid #2c3e50;
  margin-right:4px;
}

  .audio-icon,.sound-icon {
            display: inline-block;
            width: 50px;
            height: 50px;
            background-size: contain;
            cursor: pointer;
        }
         .responsive-image-container {
      
        width: 100%;

          }
         .hint{
             display: inline-block;
    position: absolute;
    right: 70px;
    top: 7px;
         }
        .container {
            max-width: 700px;
            background: #ffffff;
            /*padding: 50px 30px;*/
            border-radius: 12px;
            box-shadow: 1px 1px 8px rgb(0 0 0 / 78%);
            animation: fadeIn 1s ease-out;
            width: 100%;
        }

      
        
        .btn {
            display: inline-block;
            padding: 12px 25px;
            font-size: 1rem;
            font-weight: 500;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            min-width: 160px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }

        .btn:hover {
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
            transform: translateY(-3px);
        }

        .btn-primary {
            background-color: #2c3e50;
        }

        .btn-primary:hover {
            background-color: #1a252f;
        }

        .btn-secondary {
            background-color: #b88942;
        }

        .btn-secondary:hover {
            /*background-color: #a0784b;*/
                background-color: #a16e35;

        }
        .ar-btn{
       font-family: Almarai !important;

        }
.responsive-image-word {
    height: 220px !important;
    margin-bottom: 20px;
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    overflow: hidden;
    position: relative;
}
 .responsive-image-word img {
    max-width: 75%;
    height: 100%;
    max-height: 210px !important;
    height: auto !important;
}
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
.content{
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    /* width: 100%; */
    /* height: auto; */
    margin-top: 70px;
    margin-bottom: 47px;
}
.content .title{
        margin-left: 0;
        margin-left: 24px;
    background-color: #1f4468;
    border-radius: 6px;
    color: white;
    padding-top: 2px;
    padding-bottom: 2px;
    padding-left: 13px;
    padding-right: 13px;
border: 4px solid #b88942;
}
.center-img{
        position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 17%;
    height: auto;
}

#sound_div{
    position: absolute;
    /* float: left; */
    display: inline-block;
    left: 62px;
    top: 10px;
}


 .back_but {
            color: #1f4468;
            border: 1px solid #1f4468;
            border-radius: 4px;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            padding: 5px 10px;
            background-color: transparent;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        .back_but svg {
            margin-right: 2px;
            margin-top: 2px;
        }
        .cat_name {
            font-size: 22px;
            font-family: 'Poppins', sans-serif;
            color: white;
            background-color: #1f4468;
            padding: 5px 10px;
            border-radius: 4px;
            border: 1px solid #1f4468;
            text-align: center;
            display: inline-block;
                margin-bottom: 0 !important;
        }
        .responsive-image-container img {
            max-width: 100%;
            height: auto;
            margin-left: 40px;
        }





        /* Responsive Design */
        @media (max-width: 768px) {
            
            
               .letter-btn {
                width: calc(100% / 7 - 5px);
                max-width: 40px;
                height: 40px;
                font-size: 1.2rem;
               box-shadow: 1px 1px 1px 1px #58595a33;
            }
            
                     .responsive-image-container {
    max-width: 100%; /* Ensures the container does not exceed the parent width */
    height: auto !important;    /* Maintains aspect ratio of the container */
    overflow: hidden; /* Ensures the image doesn't overflow the container */
  }

            .center-img{
        position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 17%;
    height: auto;
}

            

            .btn {
                font-size: 0.9rem;
                padding: 10px 20px;
            }
           
              img {
              max-width: 70%;
              height: 100%;
            }
      .responsive-image-word {
    height: 220px !important;
    margin-bottom: 20px;
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    overflow: hidden;
    position: relative;
}
 .responsive-image-word img {
    max-width: 75%;
    height: 100%;
    max-height: 210px !important;
    height: auto !important;
}
            
              #display_correct_word{
              color: #e33131;
    font-size: 20px;
    /*font-family:  cursive!important;*/
        font-family: Arial!important;

      }
    .win_img{
            position: absolute;
    top: 50px;
    right: 44px;
    width: auto;
    height: auto;
        }
 .lose_img{
                position: absolute;
    top: 0px;
    right: 80px;
    width: 100%;
    height: 100%;
        }
        }

        @media (max-width: 480px) {
            
            
               .letter-btn {
                
               width: calc(100% / 7 - 5px);
                max-width: 40px;
                height: 40px;
                font-size: 1.2rem;
               box-shadow: 1px 1px 1px 1px #58595a33;
            
            }
                     .responsive-image-container {
    max-width: 100%; /* Ensures the container does not exceed the parent width */
    height: auto !important;    /* Maintains aspect ratio of the container */
    overflow: hidden; /* Ensures the image doesn't overflow the container */
  }

                      .center-img{
        position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 18%;
    height: auto;
}
            .win-img{
            position: absolute;
    top: 50px;
    right: 44px;
    width: auto;
    height: auto;
        }
          .lose_img{
               position: absolute;
    top: 0px;
    right: 80px;
    width: 100%;
    height: 100%;
        }
            .btn {
                width: 100%;
                font-size: 0.85rem;
            }
   
  img {
  max-width: 70%;
  height: 100%;
        }
 .responsive-image-word {
    height: 220px !important;
    margin-bottom: 20px;
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    overflow: hidden;
    position: relative;
}
 .responsive-image-word img {
    max-width: 75%;
    height: 100%;
    max-height: 210px !important;
    height: auto !important;
}
    #game-container {
    text-align: center;
    margin: 0 auto;
}

  #display_correct_word{
              color: #e33131;
    font-size: 20px;
    /*font-family:  cursive!important;*/
        font-family: Arial!important;

      }
#hangman-figure {
    margin: 20px auto;
}
#hangman-figure line, #hangman-figure circle {
    visibility: hidden;
}
#hangman-figure line[stroke="black"], #hangman-figure circle[stroke="black"] {
    visibility: visible;
}

}
</style>
<!--<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.min.js"></script>-->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

</head>
<body>
    
      <div class="container mt-4">
        <!-- Header -->
  

        
        <div class="row align-items-center">
            <!-- Home Button -->
            <div class="col-3 text-start" style="padding-left: 74px;">
                <a class="back_but" href="{{('../hangman/index')}}">
                    <svg width="12" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                        <path d="m22.833 1.255a3.326 3.326 0 0 0 -3.7.792l-11.143 11.486a3.57 3.57 0 0 0 0 4.934l11.143 11.487a3.354 3.354 0 0 0 2.417 1.046 3.408 3.408 0 0 0 1.283-.254 3.487 3.487 0 0 0 2.167-3.259v-22.973a3.487 3.487 0 0 0 -2.167-3.259z" fill="#1f4468"></path>
                    </svg>
                    Back
                </a>
            </div>
            
            <!-- Category Title (Centered) -->
            <div class="col-6 text-center">
                @if($cat_name != "Customized")
        <h3 class="cat_name"> {{$cat_name}}</h3>
        @endif
            </div>
            
            <!-- Hangman Image (Right) -->
            <div class="col-3 text-end">
                <div class="responsive-image-container">
                    <img src="{{asset('images/hangman.png')}}" alt="Hangman Image">
                </div>
            </div>
        </div>

        
        
        
        
        
    <div id="game-container">
        <input type="hidden" value="{{$category_id}}" name="category_id" id="category_id">
          <input type="hidden" value="{{$cat_name}}" name="cat_name" id="cat_name">
        <input type="hidden" value="{{$word}}" name="custom_word" id="custom_word">
                  <input type="hidden" value="{{$cat_type}}" name="cat_type" id="cat_type">
                    <input type="hidden" value="{{$unit}}" name="unit" id="unit">
                      <input type="hidden" value="{{$lesson}}" name="lesson" id="lesson">

        @if($show_image)
                <div class="responsive-image-word">
        <img src="{{$show_image}}">
        </div>
        @endif

         
      

<div class=""style="position: relative;">

<div id="sound_div" class="" style="display:none">
    <audio id="camb_sound" >
        <source src="" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>
        <div class="sound-icon" >
   <svg  width="35px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><g><path style="fill: rgb(31, 68, 104);" d="M352.408,491.948c-5.781,0-11.291-3.361-13.753-8.998c-3.317-7.592,0.147-16.435,7.739-19.751 C428.772,427.206,482,345.875,482,255.997c-0.004-89.873-53.228-171.202-135.598-207.194c-7.592-3.317-11.057-12.16-7.739-19.751 c3.317-7.592,12.16-11.06,19.751-7.739C451.71,62.079,511.996,154.198,512,255.996c0,101.804-60.289,193.926-153.594,234.693 C356.451,491.544,354.413,491.948,352.408,491.948z" fill="#4DBBEB"></path><path style="fill: rgb(31, 68, 104);" d="M352.413,378.768c-4.118,0-8.218-1.686-11.181-4.995c-5.525-6.173-5.002-15.655,1.171-21.181 c27.452-24.575,43.196-59.783,43.196-96.596c0-36.812-15.744-72.019-43.196-96.594c-6.173-5.525-6.696-15.008-1.171-21.181 c5.525-6.171,15.009-6.696,21.181-1.171c33.801,30.258,53.187,73.612,53.187,118.945c0,45.334-19.386,88.689-53.187,118.947 C359.551,377.509,355.975,378.768,352.413,378.768z" fill="#4DBBEB"></path><path style="fill: rgb(31, 68, 104);" d="M262.78,57.851c-5.042-2.556-11.093-2.059-15.65,1.284l-116.54,85.461H15c-8.283,0-15,6.716-15,15 v192.801c0,8.284,6.717,15,15,15h115.59l116.539,85.463c2.623,1.924,5.738,2.904,8.873,2.904c2.313,0,4.637-0.535,6.778-1.62 c5.042-2.554,8.22-7.728,8.22-13.38V71.231C271,65.579,267.822,60.406,262.78,57.851z" fill="#4DBBEB"></path></g><g><path style="fill: rgb(31, 68, 104);" d="M482,255.998c0,89.878-53.228,171.208-135.605,207.201c-7.592,3.316-11.057,12.159-7.739,19.751 c2.462,5.637,7.972,8.998,13.753,8.998c2.005,0,4.043-0.404,5.998-1.259C451.711,449.922,511.999,357.801,512,255.998H482z" fill="#2488FF"></path><path style="fill: rgb(31, 68, 104);" d="M342.403,352.593c-6.173,5.525-6.696,15.008-1.171,21.181c2.963,3.31,7.063,4.995,11.181,4.995 c3.562,0,7.138-1.26,10-3.824c33.801-30.258,53.187-73.613,53.187-118.946h-30C385.6,292.809,369.854,328.017,342.403,352.593z" fill="#2488FF"></path><path style="fill: rgb(31, 68, 104);" d="M0,352.397c0,8.284,6.717,15,15,15h115.59l116.539,85.463c2.623,1.924,5.738,2.904,8.873,2.904 c2.313,0,4.637-0.535,6.778-1.62c5.042-2.554,8.22-7.728,8.22-13.38V255.998H0V352.397z" fill="#2488FF"></path></g></svg>
        </div>
</div>


        <div id="word-display" class="mb-3 text-center"></div>
         <div id="display_correct_word" class="mb-3" style="display:none"></div>
         <div class="hint" id="hint-button">
           <svg width=37 id="Capa_1" enable-background="new 0 0 512 512" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="256" x2="256" y1="512" y2="0"><stop offset="0" stop-color="#fd5900"/><stop offset="1" stop-color="#ffde00"/></linearGradient><g><g><path d="m117.865 109.076c5.859 5.859 15.351 5.859 21.211 0 5.859-5.859 5.859-15.352 0-21.211l-20.889-20.889c-5.859-5.859-15.352-5.859-21.211 0s-5.859 15.352 0 21.211zm275.948.308 21.211-21.196c5.859-5.845 5.859-15.352 0-21.211s-15.352-5.859-21.211 0l-21.211 21.196c-5.859 5.845-5.859 15.352 0 21.211s15.351 5.859 21.211 0zm-137.813-49.384c8.291 0 15-6.709 15-15v-30c0-8.291-6.709-15-15-15s-15 6.709-15 15v30c0 8.291 6.709 15 15 15zm-179.971 151h-30.029c-8.291 0-15 6.709-15 15s6.709 15 15 15h30.029c8.291 0 15-6.709 15-15s-6.709-15-15-15zm389.971 0h-30c-8.291 0-15 6.709-15 15s6.709 15 15 15h30c8.291 0 15-6.709 15-15s-6.709-15-15-15zm-347.871 131.646-21.24 21.24c-5.859 5.859-5.859 15.352 0 21.211s15.351 5.859 21.211 0l21.24-21.24c5.859-5.859 5.859-15.352 0-21.211s-15.352-5.86-21.211 0zm275.742 0c-5.859-5.859-15.352-5.859-21.211 0s-5.859 15.352 0 21.211l21.24 21.24c5.859 5.859 15.351 5.859 21.211 0 5.859-5.859 5.859-15.352 0-21.211zm-53.965-222.408c-32.52-25.796-74.824-35.112-116.104-25.488-48.193 11.191-86.924 49.497-98.672 97.588-9.697 39.609-2.256 79.219 20.947 111.533 22.853 31.817 34.923 68.438 34.923 105.938 0 22.72 18.486 41.191 41.191 41.191h67.764c22.647 0 41.045-18.413 41.045-41.045 0-36.035 12.51-72.803 37.148-109.277 14.942-22.149 22.852-47.959 22.852-74.678 0-41.426-18.633-79.966-51.094-105.762zm3.399 163.638c-28.067 41.528-42.305 83.936-42.305 126.079 0 6.094-4.951 11.045-11.045 11.045h-67.764c-6.181 0-11.191-5.024-11.191-11.191 0-43.813-14.033-86.499-40.547-123.428-18.018-25.093-23.76-55.957-16.201-86.924 10.773-43.993 50.684-78.398 101.514-78.398 58.534 0 105.234 47.051 105.234 104.941 0 20.713-6.123 40.723-17.695 57.876zm-57.305 198.124h-60c-8.291 0-15 6.709-15 15s6.709 15 15 15h60c8.291 0 15-6.709 15-15s-6.709-15-15-15zm-30-301c-8.291 0-15 6.709-15 15v90c0 8.291 6.709 15 15 15s15-6.709 15-15v-90c0-8.291-6.709-15-15-15zm0 150c-8.284 0-15 6.714-15 15 0 8.284 6.716 15 15 15s15-6.716 15-15c0-8.286-6.716-15-15-15z" fill="url(#SVGID_1_)"/></g></g></svg> 
         </div>
         </div>
        <div id="keyboard"></div>
        <div id="score"></div>
        <!--<button id="hint-button" style="display:none">Get a Hint</button>-->
        
        
          <div class="button-group mt-4">
              <!--<a href={{('../hangman/index')}} class="btn btn-primary mb-3">Home</a>-->
    @if($cat_name != "Customized")

          <button id="reload-button" class="btn btn-secondary mb-3">Next</button>
          @endif
                  

  </div>
 
        <div id="hangman-figure">

<canvas id="hangman-canvas" width="200" height="250" ></canvas>

        </div>
        
        <audio id="audiocorrect">
        <source src="{{ asset('public/uploads/hangman/sounds/ping.mp3') }}" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>
    <audio id="audiowrong">
        <source src="{{ asset('public/uploads/hangman/sounds/error.mp3') }}" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>
    <audio id="audiowin">
        <source src="{{ asset('public/uploads/hangman/sounds/cheering.mp3') }}" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>
    <audio id="audiolose">
        
        <source src="{{ asset('public/uploads/hangman/sounds/roblox.mp3') }}" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>
   
  <!--       <video id="dance" width="640" height="360"  autoplay muted>-->
  <!--  Your browser does not support the video tag or the WebM format.-->
  <!--</video>-->
        <img src="{{ asset('public/uploads/hangman/videos/3-unscreen.gif') }}" class="win_img gif-img" style="display:none">
                <img src="{{ asset('public/uploads/hangman/videos/5-unscreen.gif') }}" class="lose_img gif-img" style="display:none" >

    </div>
      </div>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    
    <script>
        $(document).ready(function() {
            
            $(".audio-icon").click(function () {
                
            const audioPlayer = document.getElementById('audioPlayer');
                audioPlayer.play();
        })
        
             $(".sound-icon").click(function () {
                
            const audioPlayer = document.getElementById('camb_sound');
                audioPlayer.play();
        })
            
            
            
            
            
    let word = '';
    let guessedLetters = [];
 let currentStage = 0;
let usedWords = []; // To track used words
            let allWordsUsed = false;
            
            // Canvas setup
            const canvas = document.getElementById('hangman-canvas');
            const ctx = canvas.getContext('2d');
            ctx.strokeStyle = "black";
            ctx.lineWidth = 2;
            
            // Drawing functions
            function drawBase() {
                ctx.beginPath();
                ctx.moveTo(50, 230); // Base
                ctx.lineTo(150, 230);
                ctx.stroke();
                   ctx.beginPath();
                ctx.moveTo(100, 230); // Pole
                ctx.lineTo(100, 50);
                ctx.stroke();
            }

            // function drawPole() {
            //     ctx.beginPath();
            //     ctx.moveTo(100, 230); // Pole
            //     ctx.lineTo(100, 50);
            //     ctx.stroke();
            // }

            function drawTop() {
                ctx.beginPath();
                ctx.moveTo(100, 50); // Top beam
                ctx.lineTo(150, 50);
                ctx.stroke();
                   ctx.beginPath();
                ctx.moveTo(150, 50); // Rope
                ctx.lineTo(150, 80);
                ctx.stroke();
            }

            // function drawRope() {
            //     ctx.beginPath();
            //     ctx.moveTo(150, 50); // Rope
            //     ctx.lineTo(150, 80);
            //     ctx.stroke();
            // }

            function drawHead() {
                ctx.beginPath();
                ctx.arc(150, 100, 20, 0, Math.PI * 2, true); // Head
                ctx.stroke();
            }

            function drawBody() {
                ctx.beginPath();
                ctx.moveTo(150, 120); // Body
                ctx.lineTo(150, 170);
                ctx.stroke();
            }

            function drawLeftArm() {
                ctx.beginPath();
                ctx.moveTo(150, 130); // Left arm
                ctx.lineTo(130, 150);
                ctx.stroke();
            }

            function drawRightArm() {
                ctx.beginPath();
                ctx.moveTo(150, 130); // Right arm
                ctx.lineTo(170, 150);
                ctx.stroke();
            }

            function drawLeftLeg() {
                ctx.beginPath();
                ctx.moveTo(150, 170); // Left leg
                ctx.lineTo(130, 200);
                ctx.stroke();
            }

            function drawRightLeg() {
                ctx.beginPath();
                ctx.moveTo(150, 170); // Right leg
                ctx.lineTo(170, 200);
                ctx.stroke();
            }

            // Stages of the Hangman figure
            const hangmanStages = [
                drawBase,
                drawTop,
                drawHead,
                drawBody,
                drawLeftArm,
                drawRightArm,
                drawLeftLeg,
                drawRightLeg
            ];
            
              // Reset game
            function resetGame() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                currentStage = 0;
                guessedLetters = [];
                $('#word-display').html('');
                $('#keyboard').empty();
                 $('#display_correct_word').empty();
            }
            
//   $("#category").change(function () {
        const category_id = $('#category_id').val();
        const custom_word = $('#custom_word').val();
const cat_type = $('#cat_type').val();
        const unit = $('#unit').val();
const lesson = $('#lesson').val();

        function fetchWord(){
            
        resetGame();
        
        if (allWordsUsed) return;
    
        // $('.win_img').fadeIn(); 
        //         setTimeout(function () {
        //             $('.win_img').fadeOut();
        //         }, 6000);
        $('#hint-button').css('display', 'block');
 
    $.get('/hangman/start', {category_id:category_id,used_words: usedWords,custom_word:custom_word,cat_type:cat_type,unit:unit,lesson:lesson }, function(data) {
        word = data.word;
        var sound =data.sound;
            if (!word) {
                        allWordsUsed = true;
                        // alert('All words in this category have been used!');
                        $('#reload-button').hide();
                        $('#choose-category-button').show();
                        return;
                    }
        
        
                // Initialize the word display with underscores
                        word = data.word.toLowerCase();

           usedWords.push(word);

        // let display = word.split('').map(letter => '_').join(' ');
        let display = word.split('').map(letter => letter === ' ' ? '&nbsp;' : '_').join(' ');


        $('#word-display').html(display);
        
        if(sound){
            $('#camb_sound').attr('src','../'+sound);
            $('#sound_div').css('display','inline-block');
        }

        // Generate the keyboard
        const alphabet = 'abcdefghijklmnopqrstuvwxyz-';
        // resetGame();
        $('#keyboard').empty();
        alphabet.split('').forEach(letter => {
            // $('#keyboard').append(`<button class="letter-btn" data-letter="${letter}">${letter}</button>`);
                        $('#keyboard').append(`<button class="letter-btn" data-letter="${letter}">${letter}</button>`);

        });
        $('#hint-button').show();
    }).fail(function () {
                    // alert('Error: Unable to load word. Please try again.');
                });
        }
    // });
    fetchWord();
    
    // Handle letter guess
    $(document).on('click', '.letter-btn', function() {
        const guessedLetter = $(this).data('letter');
        $(this).prop('disabled', true);
        guessedLetters.push(guessedLetter);

        $.get('/hangman/guess', {
            word: word,
            guess: guessedLetter,
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function(response) {
            if (response.is_correct) {
                // Update the word display
// let updatedDisplay = word.split('').map( letter => letter === ' ' ? '&nbsp;&nbsp;&nbsp;' :(guessedLetters.includes(letter) ? letter : '_')).join(' ');
               
               
               let updatedDisplay = word.split('').map((letter, index) => {
                // If the character is a space, show extra spacing for readability
                if (letter === ' ') {
                    return '&nbsp;';
                }
                const isGuessed = guessedLetters.includes(letter);
                // If the letter has been guessed, then:
                if (isGuessed) {
                    return index === 0 ? letter.toUpperCase() : letter;
                } else {
                    return '_';
                }
            })
            // Join the array back into a string with spaces between each character.
            .join(' ');
               
               
                $('#word-display').html(updatedDisplay);

                // Play correct guess sound
                const audiocorrect = document.getElementById('audiocorrect');
                audiocorrect.play();
                // playSound('../public/uploads/hangman/sounds/ping.mp3');

                // Check for win
                if (!updatedDisplay.includes('_')) {
                    // confetti(); // Call confetti animation
                      confetti({
        particleCount: 100,
        spread: 70,
        origin: { x: 0.5, y: 0.5 },
         colors: ['#ff0000', '#00ff00', '#0000ff'],
    });
                    // playSound('win.mp3');
                      const audiowin = document.getElementById('audiowin');
                audiowin.play();
            $('.win_img').fadeIn(); 
                setTimeout(function () {
                    $('.win_img').fadeOut();
                }, 7000);
                
                
                                    // playSound('../public/uploads/hangman/sounds/cheering.mp3');

                    $('#keyboard button').prop('disabled', true);

                    // alert('Congratulations! You won!');
                }
            } else {
                // Play wrong guess sound and update hangman figure
                // playSound('wrong.mp3');
                const audioerror = document.getElementById('audiowrong');
                audioerror.play();
            //   playSound('../public/uploads/hangman/sounds/error.mp3');

                updateHangmanFigure();
              
            }
        }).fail(function () {
                    // alert('Error: Unable to process guess. Please try again.');
                });
    });

    // Hint functionality
    $('#hint-button').click(function() {
        $.get('/hangman/hint', {
            word: word,
            guessed_letters: guessedLetters,
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function(response) {
            // alert(`Hint: Try the letter "${response.hint}"`);
        }).fail(function () {
                    // alert('Error: Unable to fetch hint. Please try again.');
                });
    });
    
     // Reload word functionality
            $('#reload-button').click(function () {
                fetchWord();
            });

            // Navigate back to category selection
            $('#choose-category-button').click(function () {
                window.location.href = '../hangman/index';
            });



  function updateHangmanFigure() {
                if (currentStage < hangmanStages.length) {
                    hangmanStages[currentStage++]();
                } else {
                    // Game over
                      const audiolose = document.getElementById('audiolose');
                audiolose.play();
                                //   playSound('../public/uploads/hangman/sounds/roblox.mp3');
 $('.lose_img').fadeIn(); 
                setTimeout(function () {
                    $('.lose_img').fadeOut();
                }, 6000);
                    $('#display_correct_word').css('display','block');
                    $('#word-display').css('letter-spacing','3px');
                    $('#word-display').html( word.replace(/\b\w/g, char => char.toUpperCase()));
                    
                    // $('#display_correct_word').html(`Game over! ${word}.`);
                    // alert(`Game over! The word was "${word}".`);
                    
                    
                    
                    
                    
                    $('#keyboard button').prop('disabled', true);
                    loseAnimation();
                }
            }


    // Play sound
    function playSound(filename) {
        let audio = new Audio(`/sounds/${filename}`);
        audio.play();
    }
    
//     function loseEffect() {
//     let duration = 3 * 1000; // 3 seconds
//     let animationEnd = Date.now() + duration;
//     let defaults = { startVelocity: 5, spread: 60, ticks: 100, gravity: 1 };

//     function frame() {
//         confetti({
//             particleCount: 3,
//             angle: 90,
//             spread: 60,
//             origin: { x: Math.random(), y: 0 },
//             colors: ['#FF0000', '#000000'], // Red and Black for "Loss"
//             ...defaults
//         });

//         if (Date.now() < animationEnd) {
//             requestAnimationFrame(frame);
//         }
//     }

//     frame();
// }
// function loseAnimation() {
//     // Create and configure the canvas
//     const canvas = document.createElement('canvas');
//     canvas.id = "loseCanvas";
//     canvas.style.position = 'fixed';
//     canvas.style.top = '0';
//     canvas.style.left = '0';
//     canvas.style.width = '100%';
//     canvas.style.height = '100%';
//     canvas.style.zIndex = '9999';
//     canvas.style.pointerEvents = 'none';
//     document.body.appendChild(canvas);

//     const ctx = canvas.getContext('2d');
//     const particles = [];
//     const particleCount = 200;
//     const colors = ['#3498db', '#2980b9', '#1abc9c', '#16a085']; // Shades of blue/green

//     canvas.width = window.innerWidth;
//     canvas.height = window.innerHeight;

//     // Generate particles (tears)
//     for (let i = 0; i < particleCount; i++) {
//         particles.push({
//             x: Math.random() * canvas.width,
//             y: Math.random() * canvas.height - canvas.height,
//             speedY: Math.random() * 2 + 1,
//             radius: Math.random() * 3 + 1,
//             color: colors[Math.floor(Math.random() * colors.length)]
//         });
//     }

//     // Animation loop
//     function animate() {
//         ctx.clearRect(0, 0, canvas.width, canvas.height);

//         particles.forEach((p) => {
//             // Draw the particle
//             ctx.beginPath();
//             ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
//             ctx.fillStyle = p.color;
//             ctx.fill();

//             // Move the particle
//             p.y += p.speedY;

//             // Reset particle if it falls off screen
//             if (p.y > canvas.height) {
//                 p.y = -p.radius;
//                 p.x = Math.random() * canvas.width;
//             }
//         });

//         requestAnimationFrame(animate);
//     }

//     // Start animation
//     animate();

//     // Remove canvas after animation ends (5 seconds)
//     setTimeout(() => {
//         document.body.removeChild(canvas);
//     }, 5000);
// }
function loseAnimation() {
    // Create and configure the canvas
    const canvas = document.createElement('canvas');
    canvas.id = "loseCanvas";
    canvas.style.position = 'fixed';
    canvas.style.top = '0';
    canvas.style.left = '0';
    canvas.style.width = '100%';
    canvas.style.height = '100%';
    canvas.style.zIndex = '9999';
    canvas.style.pointerEvents = 'none';
    document.body.appendChild(canvas);

    const ctx = canvas.getContext('2d');
    const particles = [];
    const particleCount = 150; // Number of blood drops
    const colors = ['#8B0000', '#FF0000', '#B22222', '#DC143C']; // Blood-red shades

    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    // Generate particles (blood drops)
    for (let i = 0; i < particleCount; i++) {
        particles.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height - canvas.height,
            speedY: Math.random() * 3 + 2, // Faster falling
            width: Math.random() * 3 + 1, // Slightly elongated drops
            height: Math.random() * 10 + 5,
            color: colors[Math.floor(Math.random() * colors.length)]
        });
    }

    // Animation loop
    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        particles.forEach((p) => {
            // Draw the particle (elongated blood drop)
            ctx.beginPath();
            ctx.fillStyle = p.color;
            ctx.fillRect(p.x, p.y, p.width, p.height);

            // Move the particle
            p.y += p.speedY;

            // Reset particle if it falls off screen
            if (p.y > canvas.height) {
                p.y = -p.height;
                p.x = Math.random() * canvas.width;
            }
        });

        requestAnimationFrame(animate);
    }

    // Start animation
    animate();

    // Remove canvas after animation ends (5 seconds)
    setTimeout(() => {
        document.body.removeChild(canvas);
    }, 5000);
}

});

    </script>
    
    
</body>
</html>
