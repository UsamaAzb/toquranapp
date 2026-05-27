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
         font-family: 'Inter', system-ui, -apple-system, 'Helvetica Neue', sans-serif; !important;
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
            right: -167px;
            width: 30%;
            height: 47%;
        }
        .lose_img{
                position: absolute;
    top: -21px;
    right: -148px;
    width: 30%;
    height: 65%;
}
        }
        #display_correct_word{
            color: #e33131;
            font-size: 20px;
            /*font-family:  cursive!important;*/
       }
        #keyboard{
            padding-left: 60px;
            padding-right: 60px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 11px;
            width: 100%;
            max-width: 600px; /* Adjust based on screen size */
            margin: auto;
            padding: 10px;
            padding-left: 54px;

        }
        .letter-btn{
            display: flex;
            align-items: center;
            justify-content: center;
            width: calc(100% / 10 - 5px); /* Adjust grid size dynamically */
            max-width: 50px;
            height: 50px;
            font-size: 1.5rem;
            /*font-weight: bold;*/
            /*text-transform: uppercase;*/
            background-color: #fff;
            /*border: 2px solid #bb750d;*/
           border:1px solid #ff5e7b;
            border-radius: 5px;
            cursor: pointer;
            /*box-shadow: 1px 1px 1px 1px #58595a33;*/
        }
        .letter-btn:hover{
            box-shadow:none;
        }
        .letter-btn:disabled,
        .letter-btn[disabled] {
             background-color: #bf0303   !important;
            color: white!important;
            box-shadow: none;
            border:none;
        }
        .hint:disabled,.hint[disabled] {
                cursor: not-allowed!important;

        }
        #word-display{
            /*font-size: 33px;*/
                font-size: clamp(18px, 5vw, 33px);

            /*color: #b88942;*/
            color:black;
            display:inline-flex;
            display: flex;
    align-items: center;
    /*padding-bottom: 23px;*/
    font-weight: 600;
        }
        .audio-icon,.sound-icon {
            /*display: inline-block;*/
            width: 50px;
            height: 50px;
            background-size: contain;
            cursor: pointer;
             display: flex;
            align-items: center; 
              
            justify-content: center;
        }
        .word-sound{
    display: flex;
            align-items: center; /* Vertical centering */
            justify-content: center; /* Horizontal centering */
            gap: 15px; /* Space between word and icon */
            padding: 10px;
            /*max-width: 90%;*/
            margin: auto;
            margin-top: 45px;
                margin-bottom: 45px;
    background-color: #098bd10f;
}
        .hint{
            display: inline-block;
            position: absolute;
            left: 16px;
            top: 0px;
            cursor: pointer;
            background-color: white;
    border: none;
         }
        .container {
            max-width: 700px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 1px 1px 8px rgb(0 0 0 / 78%);
            animation: fadeIn 1s ease-out;
            width: 100%;
            position: relative;
        }

      
        
        .btn {
            display: inline-block;
            /*padding: 12px 25px;*/
            font-size: 1rem;
            font-weight: 500;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            /*min-width: 160px;*/
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
            background-color: #4c4b4b;
                padding: 10px 20px !important;
        }

        .btn-secondary:hover {
            /*background-color: #a0784b;*/
                background-color: #a16e35;

        }
        .ar-btn{
       font-family: Almarai !important;

        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }



#sound_div{
    /*position: absolute;*/
    /*display: inline-block;*/
    /*    left: 12px;*/
    /*top: 1px;*/
}



 .header {
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            margin-top: 37px ;
        }
        .back_but {
             color: #1f4468;
            /*border: 1px solid #1f4468;*/
            border-radius: 4px;
            font-size: 13px;
            /*font-family: 'Poppins', sans-serif;*/
            padding: 5px 10px;
            background-color: transparent;
            text-decoration: none;
            position: absolute;
            left: 4px;
                top:-28px;

            
        }
         .back_but svg {
            margin-right: 2px;
            margin-top: -2px;
        }
        .title {

            
             font-size: 20px;
            /*font-family: 'Poppins', sans-serif;*/
            color: #000000;
            /*background-color: #1f4468;*/
            padding: 7px 16px;
            /*border-radius: 4px;*/
            /*border: 1px solid #1f4468;*/
            text-align: center;
            display: inline-block;
                margin-bottom: 0 !important;
                margin-top: 30px !important;
                font-weight: 600;
        }
        .hanging-doll {
              position: absolute;
    top: 0px;
    right: -13px;
    width: 24%;
        height: auto;
        }
        .icons {
          
                position: relative;
    display: flex;
    justify-content: space-between;
    /*margin: 20px 0;*/
        }
        
        

        /* Responsive Design */
        @media (max-width: 768px) {
            
  

 
            

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
        /*font-family: Arial!important;*/

      }
    .win_img{
            position: absolute;
    top: 144px;
    right: 44px;
    width: auto;
    height: auto;
        }
 .lose_img{
                position: absolute;
     top: 117px !important;
    right: 80px;
    width: auto !important;
    height: auto !important;
        }
           #keyboard{
        
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 11px;
            width: 100%;
            max-width: 600px; /* Adjust based on screen size */
            margin: auto;
          padding: 0px !important;
          margin-top: 45px;

        }
            .letter-btn {
                
                   width: 54px !important;
                height: 47px;
                font-size: 1.2rem;
               /*box-shadow: 1px 1px 1px 1px #58595a33;*/
            
            }
            .hint{
            display: inline-block;
            position: absolute;
            left: 15px;
            top: -24px;
             cursor: pointer;

         }
         .hint svg{
           width: 40px !important;
         }
                 .word-sound {
       /*flex-direction: row; */
        /*justify-content: flex-start; */
        width: 100%; 
          display: flex;
            align-items: center; /* Vertical centering */
            justify-content: center; /* Horizontal centering */
            gap: 15px; /* Space between word and icon */
            padding: 10px;
            /*max-width: 90%;*/
            margin: auto;
            margin-top: 30px;
            margin-bottom:35px;
    }
   
        }

        @media (max-width: 480px) {

  
            .win-img{
            position: absolute;
    top: 50px;
    right: 44px;
    width: auto;
    height: auto;
        }
          .lose_img{
               position: absolute;
    top: 117px !important;
    right: 80px;
    width: auto !important;
    height: auto !important;
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
        /*font-family: Arial!important;*/

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
   .hint{
            display: inline-block;
            position: absolute;
            left: 0px;
            top: -37px;
             cursor: pointer;

         }
         .hint svg{
           /*width: 35px !important;*/
         }
#sound_div {
    /*position: absolute;*/
    /*display: inline-block;*/
    /*    left: 0px;*/
    /*top: 1px;*/
}
#sound_div svg {
    width:27px !important;
}

   .back_but {
             color: #1f4468;
            /*border: 1px solid #1f4468;*/
            border-radius: 4px;
            font-size: 13px;
            /*font-family: 'Poppins', sans-serif;*/
            padding: 5px 10px;
            background-color: transparent;
            text-decoration: none;
            position: absolute;
            left: -8px;
                top: -31px;

            
        }
        .back_but svg{
            /*width:32px!important;*/
        }
        
        
         #keyboard{
        
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 11px;
            width: 100%;
            max-width: 600px; /* Adjust based on screen size */
            margin: auto;
          padding: 0px !important;
           margin-top: 27px;

        }
            .letter-btn {
                
                   width: 54px !important;
                height: 47px;
                font-size: 1.2rem;
               /*box-shadow: 1px 1px 1px 1px #58595a33;*/
            
            }
             .word-sound {
       /*flex-direction: row; */
        /*justify-content: flex-start; */
        width: 100%; 
          display: flex;
            align-items: center; /* Vertical centering */
            justify-content: center; /* Horizontal centering */
            gap: 15px; /* Space between word and icon */
            padding: 10px;
            /*max-width: 90%;*/
            /*margin: auto;*/
            margin-top: 30px;
                margin-bottom: 45px;

    }

    .sound-icon {
        width: 25px; /* Smaller size for smaller screens */
        height: 25px;
    }

    /*#word-display {*/
    /*    font-size: 100% !important; */
    /*    padding-bottom:7px!important;*/
    /*}*/
        .title {
    font-size: 15px !important;}
}
   
</style>
<!--<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.min.js"></script>-->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

</head>
<body>
    
      <div class="container mt-4">
        <!-- Header -->
   <div class="header ">
     
              
              <a class="back_but" href="{{('../hangman/index')}}" title="back">
        <svg width=40 id="_x31_08" enable-background="new 0 0 512 512" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path id="Background" d="m256 6c138.1 0 250 111.9 250 250 0 138.1-111.9 250-250 250-138.1 0-250-111.9-250-250 0-138.1 111.9-250 250-250z" fill="#2196f3" style="fill: rgb(191, 3, 3);"></path><g id="_x31_08_1_" fill="#fff"><path id="Arrow02_5_" d="m190.2 227.3 76.4-66c6.7-5.7 17.2-1.1 17.2 7.9v35.9c56 4.4 97.2 22.7 97.2 79.7 0 26.6-17.2 53-36.2 66.8-5.9 4.3-14.4-1.1-12.2-8.1 16.7-53.4-1.7-73.6-48.9-79v36.6c0 9-10.5 13.7-17.2 7.9l-76.4-66c-4.8-4.1-4.8-11.5.1-15.7z" fill="#fff"></path><path id="Arrow01_5_" d="m134.6 243 76.4 66c6.6 5.7 17.2 1.1 17.2-7.9 0-4.3-1.9-8.5-5.2-11.3l-41.9-36.2c-5.4-4.6-8.4-11.3-8.4-18.4s3.1-13.8 8.4-18.4l41.9-36.2c3.3-2.8 5.2-7 5.2-11.3 0-9-10.5-13.7-17.2-7.9l-76.4 66c-4.8 4.1-4.8 11.5 0 15.6z" fill="#fff"></path></g></svg>
              </a>
              
                
            <div class="title">
                @if($cat_name != "Customized")
         {{$cat_name}}
        @endif
        </div>
        
        </div>
                    <img src="{{asset('images/hangman.png')}}" alt="Hangman Image" class="hanging-doll">
 <img src="{{ asset('public/uploads/hangman/videos/3-unscreen.gif') }}" class="win_img gif-img" style="display:none">
                <img src="{{ asset('public/uploads/hangman/videos/5-unscreen.gif') }}" class="lose_img gif-img" style="display:none" >
       
    <div id="game-container">
     

        @if($show_image)
                <div class="responsive-image-word">
        <img src="{{$show_image}}">
        </div>
        @endif

         <div class="word-sound">
      
<!--sound-->
<div id="sound_div" class="" style="display:none" title="audio">
    <audio id="camb_sound" >
        <source src="" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>
        <div class="sound-icon" >
   <svg  width="32px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><g><path style="fill: rgb(31, 68, 104);" d="M352.408,491.948c-5.781,0-11.291-3.361-13.753-8.998c-3.317-7.592,0.147-16.435,7.739-19.751 C428.772,427.206,482,345.875,482,255.997c-0.004-89.873-53.228-171.202-135.598-207.194c-7.592-3.317-11.057-12.16-7.739-19.751 c3.317-7.592,12.16-11.06,19.751-7.739C451.71,62.079,511.996,154.198,512,255.996c0,101.804-60.289,193.926-153.594,234.693 C356.451,491.544,354.413,491.948,352.408,491.948z" fill="#4DBBEB"></path><path style="fill: rgb(31, 68, 104);" d="M352.413,378.768c-4.118,0-8.218-1.686-11.181-4.995c-5.525-6.173-5.002-15.655,1.171-21.181 c27.452-24.575,43.196-59.783,43.196-96.596c0-36.812-15.744-72.019-43.196-96.594c-6.173-5.525-6.696-15.008-1.171-21.181 c5.525-6.171,15.009-6.696,21.181-1.171c33.801,30.258,53.187,73.612,53.187,118.945c0,45.334-19.386,88.689-53.187,118.947 C359.551,377.509,355.975,378.768,352.413,378.768z" fill="#4DBBEB"></path><path style="fill: rgb(31, 68, 104);" d="M262.78,57.851c-5.042-2.556-11.093-2.059-15.65,1.284l-116.54,85.461H15c-8.283,0-15,6.716-15,15 v192.801c0,8.284,6.717,15,15,15h115.59l116.539,85.463c2.623,1.924,5.738,2.904,8.873,2.904c2.313,0,4.637-0.535,6.778-1.62 c5.042-2.554,8.22-7.728,8.22-13.38V71.231C271,65.579,267.822,60.406,262.78,57.851z" fill="#4DBBEB"></path></g><g><path style="fill: rgb(31, 68, 104);" d="M482,255.998c0,89.878-53.228,171.208-135.605,207.201c-7.592,3.316-11.057,12.159-7.739,19.751 c2.462,5.637,7.972,8.998,13.753,8.998c2.005,0,4.043-0.404,5.998-1.259C451.711,449.922,511.999,357.801,512,255.998H482z" fill="#2488FF"></path><path style="fill: rgb(31, 68, 104);" d="M342.403,352.593c-6.173,5.525-6.696,15.008-1.171,21.181c2.963,3.31,7.063,4.995,11.181,4.995 c3.562,0,7.138-1.26,10-3.824c33.801-30.258,53.187-73.613,53.187-118.946h-30C385.6,292.809,369.854,328.017,342.403,352.593z" fill="#2488FF"></path><path style="fill: rgb(31, 68, 104);" d="M0,352.397c0,8.284,6.717,15,15,15h115.59l116.539,85.463c2.623,1.924,5.738,2.904,8.873,2.904 c2.313,0,4.637-0.535,6.778-1.62c5.042-2.554,8.22-7.728,8.22-13.38V255.998H0V352.397z" fill="#2488FF"></path></g></svg>
        </div>
</div>

     <div id="word-display" class=" text-center "></div>
                 
    </div>             
                 
                 
                 
                 
                 <div class="icons mt-4">

         <button class="hint" id="hint-button" style="display:none" title="hint">
       <svg width=40 clip-rule="evenodd" fill-rule="evenodd" image-rendering="optimizeQuality" shape-rendering="geometricPrecision" text-rendering="geometricPrecision" viewBox="0 0 2666.66 2666.66" xmlns="http://www.w3.org/2000/svg" xmlns:xodm="http://www.corel.com/coreldraw/odm/2003"><g id="Layer_x0020_1"><circle cx="1333.33" cy="1333.33" fill="#f06730" r="1333.33" style="fill: rgb(255, 167, 15);"></circle><g fill="#fefefe"><path d="m1462.46 1983.75h-258.26c-59.76 0-109.78-42.52-121.41-98.89h501.11c-11.65 56.37-61.68 98.89-121.43 98.89zm-382.33-138.14c-.24-23.66-1.11-45.59-2.5-66.09h511.41c-1.39 20.5-2.26 42.43-2.5 66.09h-506.42zm-5.96-105.34c-32.38-290.96-184.36-243.37-184.36-509.17 0-244.56 198.97-443.53 443.53-443.53s443.53 198.97 443.53 443.53c0 265.78-151.96 218.16-184.35 509.17h-150.39l45.96-468.13c1.17-11.99-8.72-22.27-20.83-21.51l-206.04 13.55c-5.82.45-57.52 5.38-81.09 42.29-13.07 20.48-14.42 45.43-4.04 74.18l67.43 359.62h-169.37zm328.52 0h-119.23l-70-371.75c-16.68-44.58 16.5-62.65 50.54-65.18l182.78-12.01-44.08 448.94z" fill="#fefefe"></path><path d="m1341.15 2118.1h-15.65c-68.36 0-123.96-55.61-123.96-123.98 0-20.03-4.99-49.62 19.63-49.62h224.33c24.61 0 19.63 29.6 19.63 49.62 0 68.37-55.61 123.98-123.98 123.98z" fill="#fefefe"></path><path d="m1333.33 700.38c-19.86 0-35.98-16.13-35.98-35.98v-96.51c0-47.39 71.96-47.39 71.96 0v96.51c0 19.86-16.12 35.98-35.98 35.98zm360.62 96.63c-27.55 0-44.92-30.12-31.14-53.98l48.26-83.57c23.62-40.9 86.1-5.2 62.32 35.98l-48.27 83.57c-6.44 11.16-18.33 17.99-31.17 17.99zm264.04 264.01c-36.61 0-49.73-48.82-18.01-67.14l83.59-48.26c40.93-23.63 77.15 38.58 35.98 62.32-19.22 11.08-83.78 53.09-101.56 53.09zm193.13 360.62h-96.52c-47.39 0-47.37-71.96 0-71.96h96.52c47.38 0 47.38 71.96 0 71.96zm-109.57 408.91c-17.78 0-82.35-42.01-101.56-53.08-40.96-23.62-5.17-86.08 35.98-62.33l83.59 48.27c31.73 18.32 18.59 67.14-18.01 67.14zm-1416.43 0c-36.61 0-49.76-48.83-18.02-67.16l83.59-48.27c40.94-23.63 77.14 38.56 35.99 62.32-19.15 11.06-83.86 53.1-101.56 53.1zm-13.05-408.91h-96.53c-47.38 0-47.38-71.96 0-71.96h96.52c47.37 0 47.37 71.96.02 71.96zm96.6-360.62c-17.77 0-82.37-42.01-101.57-53.09-40.91-23.61-5.21-86.1 35.98-62.31l83.59 48.26c31.72 18.32 18.6 67.14-18.01 67.14zm264.04-264.01c-12.85 0-24.74-6.83-31.19-17.99l-48.27-83.57c-23.65-40.95 38.56-77.13 62.32-35.98l48.26 83.57c13.77 23.85-3.58 53.98-31.13 53.98z" fill="#fefefe"></path></g></g></svg>
         </button>
         </div>

         
        <div id="keyboard"></div>
        <div id="score"></div>

        
          <div class="button-group mt-4">
              <!--<a href={{('../hangman/index')}} class="btn btn-primary mb-3">Home</a>-->
    @if($cat_name != "Customized")

          <button id="reload-button" class="btn btn-secondary mb-3">Next</button>
          @endif
                  

  </div>
    <input type="hidden" value="{{$category_id}}" name="category_id" id="category_id">
    <input type="hidden" value="{{$cat_name}}" name="cat_name" id="cat_name">
    <input type="hidden" value="{{$word}}" name="custom_word" id="custom_word">
    <input type="hidden" value="{{$cat_type}}" name="cat_type" id="cat_type">
     <input type="hidden" value="{{$unit}}" name="unit" id="unit">
      <input type="hidden" value="{{$lesson}}" name="lesson" id="lesson">
       <input type="hidden" value="{{$level}}" name="level_type" id="level_type">

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
                 $('#hint-button').prop('disabled', false);
                  hint_click=0;
                num_click=0; 
            }
            
//   $("#category").change(function () {
        const category_id = $('#category_id').val();
        const custom_word = $('#custom_word').val();
        const cat_type = $('#cat_type').val();
        const unit = $('#unit').val();
        const lesson = $('#lesson').val();
        const level = $('#level_type').val();

     var hint_click=0;
var num_click=0;    
        function fetchWord(){
   
        resetGame();
        
        if (allWordsUsed) return;
    
 
                 

    $.get('/hangman/start', {level:level,category_id:category_id,used_words: usedWords,custom_word:custom_word,cat_type:cat_type,unit:unit,lesson:lesson }, function(data) {
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
        // show word as underscores
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
       
        
    
           if(level=="easy") 
      {
              var new_word=word;
              new_word=  new_word.replace(/\s/g, '');
              var new_alphabet = 'aoeiuybcdfghjklmnpqrstvwxz';
              var filtered_alphabet = new_alphabet.split('').filter(function (char) {
        return !new_word.includes(char);
    }).join('');

    // اختيار 8 أحرف من الأبجدية المصفاة وإضافتها إلى الكلمة
    var additional_chars = filtered_alphabet.substring(0, 9);
    var new_word = new_word + additional_chars;
   
    // خلط الأحرف وإضافتها إلى الكيبورد
    $('#keyboard').empty();
    new_word.split('').sort(() => Math.random() - 0.5).forEach(letter => {
        $('#keyboard').append(`<button class="letter-btn" data-letter="${letter}">${letter}</button>`);
    });
      }
      else if(level=="medium"){
         var new_word_med=word;
              new_word_med=  new_word_med.replace(/\s/g, '');
              var new_alphabet = 'aoeiuybcdfghjklmnpqrstvwxz';
              var filtered_alphabet = new_alphabet.split('').filter(function (char) {
        return !new_word_med.includes(char);
    }).join('');

    // اختيار 3 أحرف من الأبجدية المصفاة وإضافتها إلى الكلمة
    var additional_chars = filtered_alphabet.substring(0, 3);
    var new_word_med = new_word_med + additional_chars;
    // خلط الأحرف وإضافتها إلى الكيبورد
    $('#keyboard').empty();
    new_word_med.split('').sort(() => Math.random() - 0.5).forEach(letter => {
        $('#keyboard').append(`<button class="letter-btn" data-letter="${letter}">${letter}</button>`);
    });


      }
      
        else if (level=="hard")
      {
            alphabet.split('').forEach(letter => {
            $('#keyboard').append(`<button class="letter-btn" data-letter="${letter}">${letter}</button>`);
            });
      }
    }).fail(function () {
                    // alert('Error: Unable to load word. Please try again.');
                });
        }
    // });
    
    
     if(level=="easy")
      { 
          $('#hint-button').css('display', 'block');
          
      }
      else{
           $('#hint-button').css('display', 'none');
      }
    
    fetchWord();
    
    // Handle letter guess
    $(document).on('click', '.letter-btn', function() {
        const guessedLetter = $(this).data('letter');
        // $(this).prop('disabled', true);
        

// easy level
        if(level == "easy"){
            guessedLetters.push(guessedLetter);
            $(this).prop('disabled', true);
            $.get('/hangman/guess', {
            word: word,
            guess: guessedLetter,
            _token: $('meta[name="csrf-token"]').attr('content')
           }, function(response) {
            
            // the guessedLetter in the word
            if (response.is_correct) {
              $('.letter-btn[data-letter="' + guessedLetter + '"]').prop('disabled', true);
               var updatedDisplay = word.split('').map((letter, index) => {
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
            .join(' ');
                $('#word-display').html(updatedDisplay);

                // Play correct guess sound
                const audiocorrect = document.getElementById('audiocorrect');
                audiocorrect.play();

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
                    $('#keyboard button').prop('disabled', true);
                }
            } 
            
            else {
                // Play wrong guess sound and update hangman figure
              $('.letter-btn[data-letter="' + guessedLetter + '"]').prop('disabled', true);
                
                const audioerror = document.getElementById('audiowrong');
                audioerror.play();
            //   playSound('../public/uploads/hangman/sounds/error.mp3');

                updateHangmanFigure();
              
            }
        }).fail(function () {
                    // alert('Error: Unable to process guess. Please try again.');
                });
}




        else if(level == "medium"){

        $.get('/hangman/guess', {
            word: word,
            guess: guessedLetter,
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function(response) {
            
            // the guessedLetter in the word
            if (response.is_correct) {
 let foundCorrectPosition = false;
    let foundInWord = false;
    let currentIndex = word.split('').findIndex((letter, index) => guessedLetters[index] === undefined);

    let updatedDisplay = word.split('').map((letter, index) => {
        if (letter === ' ') {
            return '&nbsp;';
        }
        if (letter === guessedLetter) {
            foundInWord = true;
            if (word[currentIndex] === guessedLetter) {
                guessedLetters[currentIndex] = guessedLetter;
                foundCorrectPosition = true;
              return  currentIndex === 0 ? letter.toUpperCase() : letter
                return letter; // إذا كان الحرف في مكانه الصحيح، يتم عرضه
            } else {
                return '_'; // إذا كان الحرف موجودًا لكن في المكان الخطأ، يتم إخفاؤه
            }
        }
        //   return guessedLetters.includes(letter)? (currentIndex === 0 ? letter.toUpperCase() : letter): '_';
        return guessedLetters.includes(letter) ? letter : '_';
    }).join(' ');

    // تحديث عرض الكلمة
    $('#word-display').html(updatedDisplay);

    if (foundCorrectPosition) {
        $('.letter-btn[data-letter="' + guessedLetter + '"]').prop('disabled', true);
        const audiocorrect = document.getElementById('audiocorrect');
        audiocorrect.play();
    } else if (foundInWord) {
        const audioerror = document.getElementById('audiowrong');
        audioerror.play();
        updateHangmanFigure();
    } else {
        $('.letter-btn[data-letter="' + guessedLetter + '"]').prop('disabled', true);
        const audioerror = document.getElementById('audiowrong');
        audioerror.play();
        updateHangmanFigure();
    }
             
             
             
             
//              let foundCorrectPosition = false;
//     let foundInWord = false;

//     // الحصول على أول موضع غير مكتمل في الكلمة
//     let currentIndex = word.split('').findIndex((letter, index) => guessedLetters[index] === undefined);
// console.log(currentIndex);
// console.log(word[currentIndex-1]);
//     if (word[currentIndex-1] === guessedLetter) {
//         foundCorrectPosition = true;
//         guessedLetters[currentIndex] = guessedLetter;
//     } else if (word.includes(guessedLetter)) {
//         foundInWord = true;
//     }
// console.log(foundCorrectPosition);
//     let updatedDisplay = word.split('').map((letter, index) => 
//         guessedLetters[index] ? guessedLetters[index] : '_'
//     ).join(' ');
// console.log(updatedDisplay);
//     $('#word-display').html(updatedDisplay);

//     if (foundCorrectPosition) {
//         $('.letter-btn[data-letter="' + guessedLetter + '"]').prop('disabled', true);
//         const audiocorrect = document.getElementById('audiocorrect');
//         audiocorrect.play();
//     } else if (foundInWord) {
//         const audioerror = document.getElementById('audiowrong');
//         audioerror.play();
//         updateHangmanFigure();
//     } else {
//         $('.letter-btn[data-letter="' + guessedLetter + '"]').prop('disabled', true);
//         const audioerror = document.getElementById('audiowrong');
//         audioerror.play();
//         updateHangmanFigure();
//     }

             
             
             

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
                    $('#keyboard button').prop('disabled', true);
                }
            } 
            
            else {
            $('.letter-btn[data-letter="' + guessedLetter + '"]').prop('disabled', true);
        const audioerror = document.getElementById('audiowrong');
        audioerror.play();
        updateHangmanFigure();
        }
        }).fail(function () {
                    // alert('Error: Unable to process guess. Please try again.');
                });
}




    });
   
    // Hint functionality
    $('#hint-button').click(function() {
        
          num_click++;
            var length_word=word.length;
            // if(length_word <= 3){
            //     hint_click=1;
            // }
            // else if((length_word >= 4) && (length_word <= 6)){
            //     hint_click=2;
            // }
            if(length_word <= 5){
                hint_click=1;
            }
          else if(length_word >= 6){
                hint_click=2;
            }
            if(num_click == hint_click){
                $('#hint-button').prop('disabled', true);
            }
        
        if( num_click <= hint_click){
        $.get('/hangman/hint', {
            word: word,
            guessed_letters: guessedLetters,
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function(response) {
          
            

            var hint_letter=response.hint;
            
                    guessedLetters.push(hint_letter);
                let updatedDisplay = word.split('').map((letter, index) => {
                if (letter === ' ') {
                    return '&nbsp;';
                }
                const isGuessed = guessedLetters.includes(letter);
                if (isGuessed) {
                    return index === 0 ? letter.toUpperCase() : letter;
                } else {
                    return '_';
                }
            })
            .join(' ');
               
            $('.letter-btn[data-letter="' + hint_letter + '"]').prop('disabled', true);


             const audiocorrect = document.getElementById('audiocorrect');
                audiocorrect.play();
                $('#word-display').html(updatedDisplay);
                
                  if (!updatedDisplay.includes('_')) {
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
            
            
        }).fail(function () {
                    // alert('Error: Unable to fetch hint. Please try again.');
                });
        }
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
