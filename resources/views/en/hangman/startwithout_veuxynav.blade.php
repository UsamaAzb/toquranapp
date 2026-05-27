<!DOCTYPE html>
<html>
<head>
    <title>Hangman Game</title>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hangman</title>
    <!-- Google Fonts -->
    <link rel="shortcut icon" href="{{asset('assets/img/favicon/favicon.ico')}}">
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
#timer{
    font-family: fantasy !important;
    font-size: 25px;
    color: #bf0303;
        padding-left: 5px;



}
.timer-div{
        display: flex;
    align-items: center;
    text-align: center;
    justify-content: center;
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

        /*.btn-primary {*/
        /*    background-color: #2c3e50;*/
        /*}*/

        /*.btn-primary:hover {*/
        /*    background-color: #1a252f;*/
        /*}*/
.two-btn-group,.two-btn-group-cat {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 20px;
    flex-wrap: wrap;
}
    .btn-secondary {
    background-color: #1b365d;
    border-color: #1b365d;
    color: #fff;
    font-size: 18px !important;
    font-weight: 600;
    border-radius: 8px;
    transition: all .3s ease;
    padding-left: 22px !important;
    padding-right: 22px !important;
        }
.btn-primary {
  background-color: #d4af37;
    border-color: #d4af37;
    color: #1b365d;
    font-weight: 600;
    padding: 8px 45px !important;
    border-radius: 8px;
    transition: all .3s ease;
    font-size: 18px !important;
}
.btn-primary:hover {
                   background-color: #b8941f;
    border-color: #b8941f;
    color: #1b365d;
    transform: translateY(-2px);

        }
        .btn-secondary:hover {
           box-shadow: 0 0 4px 1px gray;
    background-color: #1b365d;
    border-color: #1b365d;
    transform: translateY(-2px);
    color: #fff;

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
     
              
              <a class="back_but" href="{{url('game/hangman')}}" title="back">
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
     
<div class="timer-div" style="display:none">
   <svg width="45" enable-background="new 0 0 110 110" viewBox="0 0 110 110" xmlns="http://www.w3.org/2000/svg"><path d="m90.1701965 33.5512657-3.5921631-3.592165c-.6345215-.6345215-1.663269-.6345215-2.2977905 0l-7.0663452 7.0662861c-.6345215.6345215-.6345215 1.6633301 0 2.2978516l3.5921631 3.5921631c.6345215.6345215 1.663269.6345215 2.2977905 0l7.0663452-7.0662842c.6345215-.6345215.6345215-1.6633301 0-2.2978516z" fill="#344d5b"/><path d="m19.8298073 33.5512657 3.5921631-3.592165c.6345215-.6345215 1.663269-.6345215 2.2977905 0l7.0663452 7.0662861c.6345215.6345215.6345215 1.6633301 0 2.2978516l-3.5921631 3.5921631c-.6345215.6345215-1.663269.6345215-2.2977905 0l-7.0663452-7.0662842c-.6345215-.6345215-.6345215-1.6633301 0-2.2978516z" fill="#344d5b"/><path d="m51.146778 20.297846h7.706446v7.10045h-7.706446z" fill="#a64139"/><circle cx="55" cy="63.253" fill="#de594e" r="37.486"/><circle cx="55" cy="63.253" fill="#ebf0f3" r="31.362"/><g fill="#344d5b"><path d="m64.1391983 9.2616129h-18.2762413c-.8570557 0-1.5518799.6948242-1.5518799 1.5518799v10.1662607c0 .8570557.6948242 1.5518799 1.5518799 1.5518799h18.2762413c.8570557 0 1.5518799-.6948242 1.5518799-1.5518799v-10.1662607c0-.8570557-.6948242-1.5518799-1.5518799-1.5518799zm-2.182373 9.8601084h-13.9115792v-6.4499521h13.9115791v6.4499521z"/><path d="m53.785366 34.381729h2.429271v4.111074h-2.429271z"/><path d="m53.785366 88.01255h2.429271v4.111074h-2.429271z"/><path d="m80.600777 61.19714h2.429271v4.111074h-2.429271z" transform="matrix(0 1 -1 0 145.068 -18.563)"/><path d="m26.969952 61.19714h2.429271v4.111074h-2.429271z" transform="matrix(0 1 -1 0 91.437 35.068)"/><path d="m72.746727 42.235779h2.429271v4.111074h-2.429271z" transform="matrix(.707 .707 -.707 .707 52.981 -39.326)"/><path d="m34.824005 80.158501h2.429271v4.111074h-2.429271z" transform="matrix(.707 .707 -.707 .707 68.69 -1.403)"/><path d="m72.746727 80.158501h2.429271v4.111074h-2.429271z" transform="matrix(-.707 .707 -.707 -.707 184.394 88.05)"/><path d="m34.824005 42.235779h2.429271v4.111074h-2.429271z" transform="matrix(-.707 .707 -.707 -.707 92.841 50.127)"/></g><path d="m74.8819351 63.6682968c.4959488-.0551453.4959488-.7761154 0-.8312607l-27.5034981-3.0589256c-.359272-.0399323-.6735611.2412987-.6735611.6027985v5.7435188c0 .361496.3142891.6427765.6735611.6027985z" fill="#293b44"/><g transform="matrix(.987 -.16 .16 .987 -9.43 9.525)"><ellipse cx="54.362" cy="63.253" fill="#344d5b" rx="4.241" ry="4.241"/><ellipse cx="54.362" cy="63.253" fill="#ededed" rx="2.182" ry="2.182"/></g></svg>
         <span id="timer">0</span>
</div>
       

<div class="finish" style="display:none">
  <img src="{{asset('public/camb_words_api/images/good-job.webp')}}"> 
</div>



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

         <button class="hint" id="hint-button"  title="hint">
       <svg width=40 clip-rule="evenodd" fill-rule="evenodd" image-rendering="optimizeQuality" shape-rendering="geometricPrecision" text-rendering="geometricPrecision" viewBox="0 0 2666.66 2666.66" xmlns="http://www.w3.org/2000/svg" xmlns:xodm="http://www.corel.com/coreldraw/odm/2003"><g id="Layer_x0020_1"><circle cx="1333.33" cy="1333.33" fill="#f06730" r="1333.33" style="fill: rgb(255, 167, 15);"></circle><g fill="#fefefe"><path d="m1462.46 1983.75h-258.26c-59.76 0-109.78-42.52-121.41-98.89h501.11c-11.65 56.37-61.68 98.89-121.43 98.89zm-382.33-138.14c-.24-23.66-1.11-45.59-2.5-66.09h511.41c-1.39 20.5-2.26 42.43-2.5 66.09h-506.42zm-5.96-105.34c-32.38-290.96-184.36-243.37-184.36-509.17 0-244.56 198.97-443.53 443.53-443.53s443.53 198.97 443.53 443.53c0 265.78-151.96 218.16-184.35 509.17h-150.39l45.96-468.13c1.17-11.99-8.72-22.27-20.83-21.51l-206.04 13.55c-5.82.45-57.52 5.38-81.09 42.29-13.07 20.48-14.42 45.43-4.04 74.18l67.43 359.62h-169.37zm328.52 0h-119.23l-70-371.75c-16.68-44.58 16.5-62.65 50.54-65.18l182.78-12.01-44.08 448.94z" fill="#fefefe"></path><path d="m1341.15 2118.1h-15.65c-68.36 0-123.96-55.61-123.96-123.98 0-20.03-4.99-49.62 19.63-49.62h224.33c24.61 0 19.63 29.6 19.63 49.62 0 68.37-55.61 123.98-123.98 123.98z" fill="#fefefe"></path><path d="m1333.33 700.38c-19.86 0-35.98-16.13-35.98-35.98v-96.51c0-47.39 71.96-47.39 71.96 0v96.51c0 19.86-16.12 35.98-35.98 35.98zm360.62 96.63c-27.55 0-44.92-30.12-31.14-53.98l48.26-83.57c23.62-40.9 86.1-5.2 62.32 35.98l-48.27 83.57c-6.44 11.16-18.33 17.99-31.17 17.99zm264.04 264.01c-36.61 0-49.73-48.82-18.01-67.14l83.59-48.26c40.93-23.63 77.15 38.58 35.98 62.32-19.22 11.08-83.78 53.09-101.56 53.09zm193.13 360.62h-96.52c-47.39 0-47.37-71.96 0-71.96h96.52c47.38 0 47.38 71.96 0 71.96zm-109.57 408.91c-17.78 0-82.35-42.01-101.56-53.08-40.96-23.62-5.17-86.08 35.98-62.33l83.59 48.27c31.73 18.32 18.59 67.14-18.01 67.14zm-1416.43 0c-36.61 0-49.76-48.83-18.02-67.16l83.59-48.27c40.94-23.63 77.14 38.56 35.99 62.32-19.15 11.06-83.86 53.1-101.56 53.1zm-13.05-408.91h-96.53c-47.38 0-47.38-71.96 0-71.96h96.52c47.37 0 47.37 71.96.02 71.96zm96.6-360.62c-17.77 0-82.37-42.01-101.57-53.09-40.91-23.61-5.21-86.1 35.98-62.31l83.59 48.26c31.72 18.32 18.6 67.14-18.01 67.14zm264.04-264.01c-12.85 0-24.74-6.83-31.19-17.99l-48.27-83.57c-23.65-40.95 38.56-77.13 62.32-35.98l48.26 83.57c13.77 23.85-3.58 53.98-31.13 53.98z" fill="#fefefe"></path></g></g></svg>
         </button>
         </div>

         
        <div id="keyboard"></div>
        <div id="score"></div>

        
          <div class="button-group mt-4">
              <!--<a href={{('../hangman/index')}} class="btn btn-primary mb-3">Home</a>-->
   @if($cat_name != "Customized")

          <!--<button id="reload-button" class="btn btn-secondary mb-3">Next</button>-->
           <div class="two-btn-group-cat"  >

  <button id="try-again-button" class="btn btn-secondary " style="display:none" >
    Try Again
  </button>

  <button  class="btn btn-primary " id="reload-button" >
    Next
  </button>

</div>
          
          @endif
                  
 @if($cat_name == "Customized")
 <div class="two-btn-group" style="display:none" >

  <button id="try-again" class="btn btn-secondary ">
    Try Again
  </button>

  <a href="{{ url('game/hangman') }}" class="btn btn-primary ">
    Next
  </a>

</div>

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
<div id="hangman-show" width="200" height="250" ></div>
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
            
            console.log('eeee');
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
                
            //   $('#hangman-show').html('<img id="gifImage" src="../public/uploads/hangman/videos/h1.mp4" alt="Loading GIF">');
            
            //   $('#hangman-show').html('<video autoplay muted style="width:100%; height:200px;" oncontextmenu="return false;"><source src="../public/uploads/hangman/videos/h1.mp4" type="video/mp4"></video>');
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
                // $('#hangman-show').html('<img id="gifImage" src="../public/uploads/hangman/videos/h2.mp4" alt="Loading GIF">');
                // $('#hangman-show').html('<video autoplay muted style="width:100%; height:200px;" oncontextmenu="return false;"><source src="../public/uploads/hangman/videos/h2.mp4" type="video/mp4"></video>');
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
                // $('#hangman-show').html('<img id="gifImage" src="../public/uploads/hangman/videos/h3.mp4" alt="Loading GIF">');
                // $('#hangman-show').html('<video autoplay muted style="width:100%; height:200px;" oncontextmenu="return false;"><source src="../public/uploads/hangman/videos/h3.mp4" type="video/mp4"></video>');
            }

            function drawBody() {
                ctx.beginPath();
                ctx.moveTo(150, 120); // Body
                ctx.lineTo(150, 170);
                ctx.stroke();
                // $('#hangman-show').html('<img id="gifImage" src="../public/uploads/hangman/videos/h4.mp4" alt="Loading GIF">');
                // $('#hangman-show').html('<video autoplay muted style="width:100%; height:200px;" oncontextmenu="return false;"><source src="../public/uploads/hangman/videos/h4.mp4" type="video/mp4"></video>');
            }

            function drawLeftArm() {
                ctx.beginPath();
                ctx.moveTo(150, 130); // Left arm
                ctx.lineTo(130, 150);
                ctx.stroke();
                // $('#hangman-show').html('<img id="gifImage" src="../public/uploads/hangman/videos/h5.mp4" alt="Loading GIF">');
                // $('#hangman-show').html('<video autoplay muted style="width:100%; height:200px;" oncontextmenu="return false;"><source src="../public/uploads/hangman/videos/h5.mp4" type="video/mp4"></video>');
            }

            function drawRightArm() {
                ctx.beginPath();
                ctx.moveTo(150, 130); // Right arm
                ctx.lineTo(170, 150);
                ctx.stroke();
                // $('#hangman-show').html('<img id="gifImage" src="../public/uploads/hangman/videos/h6.mp4" alt="Loading GIF">');
                // $('#hangman-show').html('<video autoplay muted style="width:100%; height:200px;" oncontextmenu="return false;"><source src="../public/uploads/hangman/videos/h6.mp4" type="video/mp4"></video>');
            }

            function drawLeftLeg() {
                ctx.beginPath();
                ctx.moveTo(150, 170); // Left leg
                ctx.lineTo(130, 200);
                ctx.stroke();
                // $('#hangman-show').html('<img id="gifImage" src="../public/uploads/hangman/videos/h7.mp4" alt="Loading GIF">');
                // $('#hangman-show').html('<video autoplay muted style="width:100%; height:200px;" oncontextmenu="return false;"><source src="../public/uploads/hangman/videos/h7.mp4" type="video/mp4"></video>');
            }

            function drawRightLeg() {
                ctx.beginPath();
                ctx.moveTo(150, 170); // Right leg
                ctx.lineTo(170, 200);
                ctx.stroke();
                // $('#hangman-show').html('<img id="gifImage" src="../public/uploads/hangman/videos/h8.mp4" alt="Loading GIF">');
                // $('#hangman-show').html('<video autoplay muted style="width:100%; height:200px;" oncontextmenu="return false;"><source src="../public/uploads/hangman/videos/h8.mp4" type="video/mp4"></video>');
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
                //  $('#hint-button').prop('disabled', false);
                         if(level=="easy")
      { 
                 $('#hint-button').prop('disabled', false);
          
      }
      else{
           $('#hint-button').prop('disabled', true);


      }
                  hint_click=0;
                num_click=0; 
                
                 $('.two-btn-group').css('display','none');
 $('#try-again-button').css('display','none');
            }
            

        const category_id = $('#category_id').val();
        const custom_word = $('#custom_word').val();
                const cat_name = $('#cat_name').val();
        
        const cat_type = $('#cat_type').val();
        const unit = $('#unit').val();
        const lesson = $('#lesson').val();
        const level = $('#level_type').val();
        var hint_click=0;
        var num_click=0; 
        var countdown;

        // try-again
        var currentWord = null;   // هنحفظ فيها الكلمة الحالية lowercase
var currentSound = null;  // هنحفظ فيها رابط الصوت لو موجود
// try-again

fetchWord();
        // start to fetch the word
        function fetchWord(){
        resetGame();
        if (allWordsUsed) return;
    
 
                 

    $.get('/hangman/start', {level:level,category_id:category_id,used_words: usedWords,custom_word:custom_word,cat_type:cat_type,unit:unit,lesson:lesson }, function(data) {
        word = data.word;
        var sound =data.sound;
        console.log(word);
        if (!word) {
                allWordsUsed = true;
                $('#reload-button').hide();
                $('#choose-category-button').show();
                $('#sound_div').css('display','none');
                $('#hint-button').css('display','none');
                $('.word-sound').css('display','none');
                $('.finish').css('display','block');
                return;
            }

 //try-again
    currentWord  = String(data.word).toLowerCase().trim();
    currentSound = sound ? String(sound) : null;
    // try-again
    word = currentWord;
                // word = data.word.toLowerCase();
                usedWords.push(word);
                let actualIndex = 0;
                  let display = word.split('').map((letter, index) => {
                if (letter === ' ') {
                    return '&nbsp;';

                }
                 else {
                     actualIndex++
                    return `<span class="display_letter" data-index="${actualIndex}">_</span>&nbsp;`;
                }
            })
            .join(' ');

                $('#word-display').html(display);
                
                // if(sound){
                    if (currentSound) {

                                var pageURL = $(location).attr("href");
if (pageURL.indexOf("camp") >= 0)
{
    $('#camb_sound').attr('src','../../../../../../'+sound);
}
else if (pageURL.indexOf("ph") >= 0)
{
    $('#camb_sound').attr('src','../../../../../../'+sound);
}
else
             {       $('#camb_sound').attr('src','../'+sound);}
                    $('#sound_div').css('display','inline-block');
                }

        // Generate the keyboard
        
        // resetGame();
        $('#keyboard').empty();
           if(level=="easy") 
      {
        
              var new_word=word;
              new_word=  new_word.replace(/\s/g, '');
              var new_alphabet = 'aoeiuybcdfghjklmnpqrstvwxz';
             
                
            //   var filtered_alphabet = new_alphabet.split('').filter(function (char) {
            //     return !new_word.includes(char);
            // }).join('');
        
            // // اختيار 8 أحرف من الأبجدية المصفاة وإضافتها إلى الكلمة
            // var additional_chars = filtered_alphabet.substring(0, 9);
            // var new_word = new_word + additional_chars;
   
            // // خلط الأحرف وإضافتها إلى الكيبورد
            // $('#keyboard').empty();
            // new_word.split('').sort(() => Math.random() - 0.5).forEach(letter => {
            //     $('#keyboard').append(`<button class="letter-btn" data-letter="${letter}">${letter}</button>`);
            // });
            // ######################################
    var wordLetters = [];
   var letterCounts = {};

for (var i = 0, index = 1; i < new_word.length; i++) {
    var letter = new_word[i];
    if (letter !== " ") { // تجاهل المسافات
        if (!letterCounts[letter]) {
            letterCounts[letter] = 0;
            wordLetters.push({ letter: letter, index: i + 1 }); // حفظ أول ظهور مع الفهرس الفعلي
        }
        letterCounts[letter]++;
    }
}

    var filtered_alphabet = new_alphabet.split('').filter(function(char) {
        return !new_word.includes(char);
    }).join('');

    var additionalChars = [];
    for (var j = 0; j < 9 && j < filtered_alphabet.length; j++) {
        additionalChars.push({ letter: filtered_alphabet[j], index: 0 });
    }

    var finalChars = wordLetters.concat(additionalChars);

    finalChars.sort(() => Math.random() - 0.5);

    $('#keyboard').empty();
    // console.log(finalChars);
    // finalChars.forEach(function(item) {
    //     $('#keyboard').append(`<button class="letter-btn" data-index="${item.index}" data-letter="${item.letter}">${item.letter}</button>`);
    // });
    finalChars.forEach(function(item) {
    var count = letterCounts[item.letter] || 0; // إذا كان من الحروف الإضافية سيكون 0
    $('#keyboard').append(`<button class="letter-btn" data-index="${item.index}" data-letter="${item.letter}" data-count="${count}">${item.letter}</button>`);
});
    
    
    


// var letterIndices = {}; // Store letter occurrences
// var wordLetters = [];

// // Assign indices to each letter in new_word
// for (var i = 0; i < new_word.length; i++) {
//     var letter = new_word[i];
//     if (!letterIndices[letter]) {
//         letterIndices[letter] = [i + 1];
//     } else {
//         letterIndices[letter].push(i + 1);
//     }
// }

// // Create word letters array with index tracking
// for (var letter in letterIndices) {
//     letterIndices[letter].forEach((index, idx) => {
//         var letterData = { letter: letter, index: index };
//         if (idx > 0) {
//             letterData[`index${idx + 1}`] = index; // Add additional index attributes
//         }
//         wordLetters.push(letterData);
//     });
// }

// // Filter alphabet for additional random letters
// var filtered_alphabet = new_alphabet.split('').filter(char => !new_word.includes(char)).join('');

// var additionalChars = [];
// for (var j = 0; j < 9 && j < filtered_alphabet.length; j++) {
//     additionalChars.push({ letter: filtered_alphabet[j], index: 0 });
// }

// // Combine word letters and additional random letters
// var finalChars = wordLetters.concat(additionalChars);
// finalChars.sort(() => Math.random() - 0.5); // Shuffle letters

// $('#keyboard').empty();
// console.log(finalChars);

// // Append buttons with multiple data-index attributes if needed
// finalChars.forEach(function (item) {
//     var button = `<button class="letter-btn" data-index="${item.index}" data-letter="${item.letter}"`;

//     // Add extra index attributes for repeated letters
//     Object.keys(item).forEach((key) => {
//         if (key.startsWith("index") && key !== "index") {
//             button += ` data-${key}="${item[key]}"`;
//         }
//     });

//     button += `>${item.letter}</button>`;

//     $('#keyboard').append(button);
// });

    
    
    
      }
      else if(level=="medium"){
         var new_word_med=word;
              new_word_med=  new_word_med.replace(/\s/g, '');
              var new_alphabet = 'aoeiuybcdfghjklmnpqrstvwxz';
              
               var wordLetters = [];
       var letterCounts = {};
for (var i = 0, index = 1; i < new_word_med.length; i++) {
    var letter = new_word_med[i];
    if (letter !== " ") { // تجاهل المسافات
        if (!letterCounts[letter]) {
            letterCounts[letter] = 0;
            wordLetters.push({ letter: letter, index: i + 1 }); // حفظ أول ظهور مع الفهرس الفعلي
        }
        letterCounts[letter]++;
    }
}

    // 2. استخراج الأحرف الإضافية التي لا تظهر في الكلمة
    var filtered_alphabet = new_alphabet.split('').filter(function(char) {
        return !new_word_med.includes(char);
    }).join('');

    // 3. اختيار 9 أحرف إضافية وتعيين data-index=0 لها
    var additionalChars = [];
    for (var j = 0; j < 9 && j < filtered_alphabet.length; j++) {
        additionalChars.push({ letter: filtered_alphabet[j], index: 0 });
    }

    // 4. دمج مصفوفات أحرف الكلمة والأحرف الإضافية
    var finalChars = wordLetters.concat(additionalChars);

    // 5. خلط المصفوفة عشوائياً
    finalChars.sort(() => Math.random() - 0.5);

    // 6. إنشاء الأزرار وإضافتها للعنصر الذي يحتوي على الـ keyboard
    $('#keyboard').empty();
      finalChars.forEach(function(item) {
    var count = letterCounts[item.letter] || 0; // إذا كان من الحروف الإضافية سيكون 0
    $('#keyboard').append(`<button class="letter-btn" data-index="${item.index}" data-letter="${item.letter}" data-count="${count}">${item.letter}</button>`);
});

      }
      
        else if (level=="hard")
      {
         
            // alphabet.split('').forEach(letter => {
            // $('#keyboard').append(`<button class="letter-btn" data-letter="${letter}">${letter}</button>`);
            // });
             $('.timer-div').css('display','flex');
             var new_word_med=word;
              new_word_med=  new_word_med.replace(/\s/g, '');
           
                  var totalSeconds = new_word_med.length * 8; // كل حرف = 4 ثواني
                            $("#timer").text("0:00");
                clearInterval(countdown); // إيقاف أي مؤقت سابق

                function formatTime(seconds) {
                    var minutes = Math.floor(seconds / 60);
                    var remainingSeconds = seconds % 60;
                    return minutes + ":" + (remainingSeconds < 10 ? "0" : "") + remainingSeconds;
                }
                
                $("#timer").text(formatTime(totalSeconds));
                
                
                 countdown = setInterval(function () {
                    if (totalSeconds > 0) {
                        totalSeconds--;
                        $("#timer").text(formatTime(totalSeconds));
                    } else {
                        clearInterval(countdown);
                         const audiolose = document.getElementById('audiolose');
                audiolose.play();
 $('.lose_img').fadeIn(); 
                setTimeout(function () {
                    $('.lose_img').fadeOut();
                }, 6000);
                    $('#display_correct_word').css('display','block');
                    $('#word-display').css('letter-spacing','3px');
                    $('#word-display').html( word.replace(/\b\w/g, char => char.toUpperCase()));
                    $('#keyboard button').prop('disabled', true);
                    loseAnimation();
                    }
                  
                }, 1000);
                
                    
                    
 
                    
                    
                    
                    
                    
                    
              var alphabet = 'abcdefghijklmnopqrstuvwxyz-';
               var wordLetters = [];
       var letterCounts = {};
for (var i = 0, index = 1; i < new_word_med.length; i++) {
    var letter = new_word_med[i];
    if (letter !== " ") {
        if (!letterCounts[letter]) {
            letterCounts[letter] = 0;
            wordLetters.push({ letter: letter, index: i + 1 }); 
        }
        letterCounts[letter]++;
    }
}

var filtered_alphabet = alphabet.split('').filter(function(char) {
    return !new_word_med.includes(char);
}).map(char => ({ letter: char, index: 0 })); // تعيين index = 0

var finalChars = wordLetters.concat(filtered_alphabet);

finalChars.sort((a, b) => a.letter.localeCompare(b.letter));

    $('#keyboard').empty();
      finalChars.forEach(function(item) {
    var count = letterCounts[item.letter] || 0; 
    $('#keyboard').append(`<button class="letter-btn" data-index="${item.index}" data-letter="${item.letter}" data-count="${count}">${item.letter}</button>`);
});

      }
    }).fail(function () {
                    // alert('Error: Unable to load word. Please try again.');
                });
        }
    // });
    // try-again
    
    $('#try-again-button').on('click', function () {
    if (!currentWord) return;

    // نفس reset اللي عندك
    resetGame();

    // ====== إعادة بناء عرض الكلمة ======
    var word = currentWord;
    let actualIndex = 0;
    let display = word.split('').map((letter, index) => {
        if (letter === ' ') {
            return '&nbsp;';
        } else {
            actualIndex++;
            return `<span class="display_letter" data-index="${actualIndex}">_</span>&nbsp;`;
        }
    }).join(' ');
    $('#word-display').html(display);

    // ====== الصوت بنفس منطق المسارات عندك ======
    if (currentSound) {
        var pageURL = $(location).attr("href");
        if (pageURL.indexOf("camp") >= 0) {
            $('#camb_sound').attr('src','../../../../../../'+currentSound);
        } else if (pageURL.indexOf("ph") >= 0) {
            $('#camb_sound').attr('src','../../../../../../'+currentSound);
        } else {
            $('#camb_sound').attr('src','../'+currentSound);
        }
        $('#sound_div').css('display','inline-block');
    } else {
        $('#sound_div').css('display','none');
    }

    // ====== بناء الكيبورد طبقًا للـ level ======
    $('#keyboard').empty();

    if(level=="easy") {
        var new_word = word.replace(/\s/g, '');
        var new_alphabet = 'aoeiuybcdfghjklmnpqrstvwxz';

        var wordLetters = [];
        var letterCounts = {};
        for (var i = 0, index = 1; i < new_word.length; i++) {
            var letter = new_word[i];
            if (letter !== " ") {
                if (!letterCounts[letter]) {
                    letterCounts[letter] = 0;
                    wordLetters.push({ letter: letter, index: i + 1 });
                }
                letterCounts[letter]++;
            }
        }

        var filtered_alphabet = new_alphabet.split('').filter(function(char) {
            return !new_word.includes(char);
        }).join('');

        var additionalChars = [];
        for (var j = 0; j < 9 && j < filtered_alphabet.length; j++) {
            additionalChars.push({ letter: filtered_alphabet[j], index: 0 });
        }

        var finalChars = wordLetters.concat(additionalChars);
        finalChars.sort(() => Math.random() - 0.5);

        $('#keyboard').empty();
        finalChars.forEach(function(item) {
            var count = letterCounts[item.letter] || 0;
            $('#keyboard').append(`<button class="letter-btn" data-index="${item.index}" data-letter="${item.letter}" data-count="${count}">${item.letter}</button>`);
        });
    }
    else if(level=="medium"){
        var new_word_med = word.replace(/\s/g, '');
        var new_alphabet = 'aoeiuybcdfghjklmnpqrstvwxz';

        var wordLetters = [];
        var letterCounts = {};
        for (var i = 0, index = 1; i < new_word_med.length; i++) {
            var letter = new_word_med[i];
            if (letter !== " ") {
                if (!letterCounts[letter]) {
                    letterCounts[letter] = 0;
                    wordLetters.push({ letter: letter, index: i + 1 });
                }
                letterCounts[letter]++;
            }
        }

        var filtered_alphabet = new_alphabet.split('').filter(function(char) {
            return !new_word_med.includes(char);
        }).join('');

        var additionalChars = [];
        for (var j = 0; j < 9 && j < filtered_alphabet.length; j++) {
            additionalChars.push({ letter: filtered_alphabet[j], index: 0 });
        }

        var finalChars = wordLetters.concat(additionalChars);
        finalChars.sort(() => Math.random() - 0.5);

        $('#keyboard').empty();
        finalChars.forEach(function(item) {
            var count = letterCounts[item.letter] || 0;
            $('#keyboard').append(`<button class="letter-btn" data-index="${item.index}" data-letter="${item.letter}" data-count="${count}">${item.letter}</button>`);
        });
    }
        else if (level=="hard")
      {
         
            // alphabet.split('').forEach(letter => {
            // $('#keyboard').append(`<button class="letter-btn" data-letter="${letter}">${letter}</button>`);
            // });
             $('.timer-div').css('display','flex');
             var new_word_med=word;
              new_word_med=  new_word_med.replace(/\s/g, '');
           
                  var totalSeconds = new_word_med.length * 8; // كل حرف = 4 ثواني
                            $("#timer").text("0:00");
                clearInterval(countdown); // إيقاف أي مؤقت سابق

                function formatTime(seconds) {
                    var minutes = Math.floor(seconds / 60);
                    var remainingSeconds = seconds % 60;
                    return minutes + ":" + (remainingSeconds < 10 ? "0" : "") + remainingSeconds;
                }
                
                $("#timer").text(formatTime(totalSeconds));
                
                
                 countdown = setInterval(function () {
                    if (totalSeconds > 0) {
                        totalSeconds--;
                        $("#timer").text(formatTime(totalSeconds));
                    } else {
                        clearInterval(countdown);
                         const audiolose = document.getElementById('audiolose');
                audiolose.play();
 $('.lose_img').fadeIn(); 
                setTimeout(function () {
                    $('.lose_img').fadeOut();
                }, 6000);
                    $('#display_correct_word').css('display','block');
                    $('#word-display').css('letter-spacing','3px');
                    $('#word-display').html( word.replace(/\b\w/g, char => char.toUpperCase()));
                    $('#keyboard button').prop('disabled', true);
                    loseAnimation();
                    }
                  
                }, 1000);
                
                    
                    
 
                    
                    
                    
                    
                    
                    
              var alphabet = 'abcdefghijklmnopqrstuvwxyz-';
               var wordLetters = [];
       var letterCounts = {};
for (var i = 0, index = 1; i < new_word_med.length; i++) {
    var letter = new_word_med[i];
    if (letter !== " ") {
        if (!letterCounts[letter]) {
            letterCounts[letter] = 0;
            wordLetters.push({ letter: letter, index: i + 1 }); 
        }
        letterCounts[letter]++;
    }
}

var filtered_alphabet = alphabet.split('').filter(function(char) {
    return !new_word_med.includes(char);
}).map(char => ({ letter: char, index: 0 })); // تعيين index = 0

var finalChars = wordLetters.concat(filtered_alphabet);

finalChars.sort((a, b) => a.letter.localeCompare(b.letter));

    $('#keyboard').empty();
      finalChars.forEach(function(item) {
    var count = letterCounts[item.letter] || 0; 
    $('#keyboard').append(`<button class="letter-btn" data-index="${item.index}" data-letter="${item.letter}" data-count="${count}">${item.letter}</button>`);
});

      }
    // متغيّراتك الأخرى (مثلاً counters) لو بتتصفر في resetGame خلاص تمام
});

    
    
    
     
    
    
    // var displayArray = new Array(word.length).fill('_');
    // عرض الكلمة المبدئي في العنصر المخصص (#word-display)
    // $('#word-display').html(displayArray.join(' '));

     
                // return;
    // Handle letter guess
    $(document).on('click', '.letter-btn', function() {
        const guessedLetter = $(this).data('letter');
        // $(this).prop('disabled', true);
         var letterIndex = $(this).attr('data-index');
    
// easy level
        if(level == "easy"){
            // guessedLetters.push(guessedLetter);
            // $(this).prop('disabled', true);
            $.get('/hangman/guess', {
            word: word,
            guess: guessedLetter,
            _token: $('meta[name="csrf-token"]').attr('content')
           }, function(response) {
            
            // the guessedLetter in the word
            if (response.is_correct) {
            //   $('.letter-btn[data-index="' + letterIndex + '"]').prop('disabled', true);
            //   var updatedDisplay = word.split('').map((letter, index) => {
            //     // If the character is a space, show extra spacing for readability
            //     if (letter === ' ') {
            //         return '&nbsp;';
            //     }
                
            //     const isGuessed = guessedLetters.includes(letter);
            //     // If the letter has been guessed, then:
            //     if (isGuessed) {
            //         return index === 0 ? letter.toUpperCase() : letter;
            //     } 
                
                
                
                
            //     else {
            //         return '_';
            //     }
            // })
            // .join(' ');
            //     $('#word-display').html(updatedDisplay);
            if(letterIndex === "1"){
                var guessedLet=guessedLetter.toUpperCase();
            }
            else{
                var guessedLet=guessedLetter;
            }
                            

          var count_letter=$(`.letter-btn[data-index="${letterIndex}"]`).attr('data-count');
          
            var foundIndex = null;
            if (count_letter > 1) {
                count_letter--;
                $(".display_letter[data-index='" + letterIndex + "']").text(guessedLet);
                var logicalIndex = 0;
             for (var i = 0; i < word.length; i++) {
                 var currentIndex = i + 1; // Since data-index starts from 1
          if (word[i] !== " ") { 
        logicalIndex++;
        var guessedLet=guessedLetter.toLowerCase();
                // if (word[i] === guessedLet && logicalIndex !== letterIndex) {
                     if (word[i] === guessedLet && logicalIndex !== letterIndex) {
                    foundIndex = logicalIndex;
                    if ($(`.display_letter[data-index="${foundIndex}"]`).text() === "_") {
                            $(`.letter-btn[data-index="${letterIndex}"]`).attr("data-count", count_letter);
                            $(`.letter-btn[data-index="${letterIndex}"]`).data('index', foundIndex)
                    .attr("data-index", foundIndex);
                    break; // Stop after finding the first match that is not excluded
                    }
                }
             }
             }

            }
            else{
                console.log(letterIndex);
            $(".display_letter[data-index='" + letterIndex + "']").text(guessedLet);
              $('.letter-btn[data-index="' + letterIndex + '"]').prop('disabled', true);
            }

  
                // Play correct guess sound
                const audiocorrect = document.getElementById('audiocorrect');
                audiocorrect.play();

                // Check for win
                if ($(".display_letter:contains('_')").length == 0) {
      
                // if (!updatedDisplay.includes('_')) {
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
                    if(cat_name == "Customized"){
                    $('.two-btn-group').css('display','block');}
                    else{
                        $('#try-again-button').css('display','block');
                    }
                }
            } 
            
            else {
                // Play wrong guess sound and update hangman figure
              $('.letter-btn[data-letter="' + guessedLetter + '"]').prop('disabled', true);
                
                const audioerror = document.getElementById('audiowrong');
                audioerror.play();
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
                
                //  $('.letter-btn[data-index="' + letterIndex + '"]').prop('disabled', true);
                    if(letterIndex === "1"){
                          var guessedLet=guessedLetter.toUpperCase();
                      }
                    else{
                        var guessedLet=guessedLetter;
                    }
                    
            var firstUnderscoreIndex = $("#word-display .display_letter:contains('_')").first().data("index");

            if(firstUnderscoreIndex == letterIndex){
                
                var count_letter=$(`.letter-btn[data-index="${letterIndex}"]`).attr('data-count');
            var foundIndex = null;
            if (count_letter > 1) {
                count_letter--;
                $(".display_letter[data-index='" + letterIndex + "']").text(guessedLet);
                var logicalIndex = 0;
                        var guessedLet=guessedLetter.toLowerCase();

             for (var i = 0; i < word.length; i++) {
                 var currentIndex = i + 1; // Since data-index starts from 1
                   

         if (word[i] !== " ") { 
        logicalIndex++;
                var guessedLet=guessedLetter.toLowerCase();

                if (word[i] === guessedLet && logicalIndex !== letterIndex) {
                    foundIndex = logicalIndex;
                    if ($(`.display_letter[data-index="${foundIndex}"]`).text() === "_") {
                            $(`.letter-btn[data-index="${letterIndex}"]`).attr("data-count", count_letter);
                            $(`.letter-btn[data-index="${letterIndex}"]`).data('index', foundIndex)
                    .attr("data-index", foundIndex);
                    break; // Stop after finding the first match that is not excluded
                    }
                }
             }
}
            }
                else{
                console.log(letterIndex);
            $(".display_letter[data-index='" + letterIndex + "']").text(guessedLet);
              $('.letter-btn[data-index="' + letterIndex + '"]').prop('disabled', true);
            }
                
                
                
                //  $('.letter-btn[data-index="' + letterIndex + '"]').prop('disabled', true);

                // $(".display_letter[data-index='" + letterIndex + "']").text(guessedLet);
                 const audiocorrect = document.getElementById('audiocorrect');
                audiocorrect.play();
            }
             else if(firstUnderscoreIndex != letterIndex){
                 const audioerror = document.getElementById('audiowrong');
                    audioerror.play();
                  updateHangmanFigure();
            }
                // Play correct guess sound
               

                // Check for win
                if ($(".display_letter:contains('_')").length == 0) {
      
                // if (!updatedDisplay.includes('_')) {
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
  if(cat_name == "Customized"){
                    $('.two-btn-group').css('display','block');}
                    else{
                        $('#try-again-button').css('display','block');
                    }                }
            } 
            
            else {
                // Play wrong guess sound and update hangman figure
              $('.letter-btn[data-letter="' + guessedLetter + '"]').prop('disabled', true);
                
                const audioerror = document.getElementById('audiowrong');
                audioerror.play();
                updateHangmanFigure();
            }
                
        }).fail(function () {
                    // alert('Error: Unable to process guess. Please try again.');
                });
                
                
                
                
                
                
                
                
//  let foundCorrectPosition = false;
//     let foundInWord = false;
//     let currentIndex = word.split('').findIndex((letter, index) => guessedLetters[index] === undefined);

//     let updatedDisplay = word.split('').map((letter, index) => {
//         if (letter === ' ') {
//             return '&nbsp;';
//         }
//         if (letter === guessedLetter) {
//             foundInWord = true;
//             if (word[currentIndex] === guessedLetter) {
//                 guessedLetters[currentIndex] = guessedLetter;
//                 foundCorrectPosition = true;
//               return  currentIndex === 0 ? letter.toUpperCase() : letter
//                 return letter; // إذا كان الحرف في مكانه الصحيح، يتم عرضه
//             } else {
//                 return '_'; // إذا كان الحرف موجودًا لكن في المكان الخطأ، يتم إخفاؤه
//             }
//         }
//         //   return guessedLetters.includes(letter)? (currentIndex === 0 ? letter.toUpperCase() : letter): '_';
//         return guessedLetters.includes(letter) ? letter : '_';
//     }).join(' ');

//     // تحديث عرض الكلمة
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
        //         if (!updatedDisplay.includes('_')) {
        //             // confetti(); // Call confetti animation
        //               confetti({
        //                 particleCount: 100,
        //                 spread: 70,
        //                 origin: { x: 0.5, y: 0.5 },
        //                  colors: ['#ff0000', '#00ff00', '#0000ff'],
        //             });
        //             // playSound('win.mp3');
        //               const audiowin = document.getElementById('audiowin');
        //                 audiowin.play();
        //                  $('.win_img').fadeIn(); 
        //                 setTimeout(function () {
        //                     $('.win_img').fadeOut();
        //                 }, 7000);
        //             $('#keyboard button').prop('disabled', true);
        //         }
        //     } 
            
        //     else {
        //     $('.letter-btn[data-letter="' + guessedLetter + '"]').prop('disabled', true);
        // const audioerror = document.getElementById('audiowrong');
        // audioerror.play();
        // updateHangmanFigure();
        // }
        // }).fail(function () {
        //             // alert('Error: Unable to process guess. Please try again.');
        //         });
}

else if(level == "hard"){

        $.get('/hangman/guess', {
            word: word,
            guess: guessedLetter,
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function(response) {
            
            // the guessedLetter in the word
            if (response.is_correct) {
                
                    if(letterIndex === "1"){
                          var guessedLet=guessedLetter.toUpperCase();
                      }
                    else{
                        var guessedLet=guessedLetter;
                    }
                    
            var firstUnderscoreIndex = $("#word-display .display_letter:contains('_')").first().data("index");

            if(firstUnderscoreIndex == letterIndex){
                
                var count_letter=$(`.letter-btn[data-index="${letterIndex}"]`).attr('data-count');
            var foundIndex = null;
            if (count_letter > 1) {
                count_letter--;
                $(".display_letter[data-index='" + letterIndex + "']").text(guessedLet);
                var logicalIndex = 0;
                        var guessedLet=guessedLetter.toLowerCase();

             for (var i = 0; i < word.length; i++) {
                 var currentIndex = i + 1; // Since data-index starts from 1
                   

         if (word[i] !== " ") { 
        logicalIndex++;
                var guessedLet=guessedLetter.toLowerCase();

                if (word[i] === guessedLet && logicalIndex !== letterIndex) {
                    foundIndex = logicalIndex;
                    if ($(`.display_letter[data-index="${foundIndex}"]`).text() === "_") {
                            $(`.letter-btn[data-index="${letterIndex}"]`).attr("data-count", count_letter);
                            $(`.letter-btn[data-index="${letterIndex}"]`).data('index', foundIndex)
                    .attr("data-index", foundIndex);
                    break; // Stop after finding the first match that is not excluded
                    }
                }
             }
}
            }
                else{
                console.log(letterIndex);
            $(".display_letter[data-index='" + letterIndex + "']").text(guessedLet);
              $('.letter-btn[data-index="' + letterIndex + '"]').prop('disabled', true);
            }
                
                
                
                //  $('.letter-btn[data-index="' + letterIndex + '"]').prop('disabled', true);

                // $(".display_letter[data-index='" + letterIndex + "']").text(guessedLet);
                 const audiocorrect = document.getElementById('audiocorrect');
                audiocorrect.play();
            }
             else if(firstUnderscoreIndex != letterIndex){
                 const audioerror = document.getElementById('audiowrong');
                    audioerror.play();
                  updateHangmanFigure();
            }
                // Play correct guess sound
               

                // Check for win
                if ($(".display_letter:contains('_')").length == 0) {
                    clearInterval(countdown);
      
              
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
                     if(cat_name == "Customized"){
                    $('.two-btn-group').css('display','block');}
                    else{
                        $('#try-again-button').css('display','block');
                    }
                }
            } 
            
            else {
                // Play wrong guess sound and update hangman figure
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
            guessedLetters=[];
$('.display_letter').each(function() {
    var letter = $(this).text().trim();
   
    if (letter !== '_') {
        letter = letter.toLowerCase();
        guessedLetters.push(letter);
    }
});
console.log(guessedLetters);
        if( num_click <= hint_click){
        $.get('/hangman/hint', {
            word: word,
            guessed_letters: guessedLetters,
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function(response) {
          
            console.log(guessedLetters);
            var hint_letter=response.hint;
            var letterIndex  = $('.letter-btn[data-letter="' + hint_letter + '"]').data("index");
                                    console.log(hint_letter);

                        console.log(letterIndex);

             if(letterIndex == 1){
                var guessedLet=hint_letter.toUpperCase();
                                        console.log(guessedLet);

            }
            else{
                var guessedLet=hint_letter;
            }

          var count_letter=$(`.letter-btn[data-index="${letterIndex}"]`).attr('data-count');
            var foundIndex = null;
            if (count_letter > 1) {
                count_letter--;
                $(".display_letter[data-index='" + letterIndex + "']").text(guessedLet);
                var logicalIndex = 0;
             for (var i = 0; i < word.length; i++) {
                 var currentIndex = i + 1; // Since data-index starts from 1
          if (word[i] !== " ") { 
        logicalIndex++;
         guessedLet=guessedLet.toLowerCase();
                                    guessedLetters.push(guessedLet);
                // guessedLetters.push(guessedLet);
 
                                    if (word[i] === guessedLet && logicalIndex !== letterIndex) {
                    foundIndex = logicalIndex;
                    if ($(`.display_letter[data-index="${foundIndex}"]`).text() === "_") {
                            $(`.letter-btn[data-index="${letterIndex}"]`).attr("data-count", count_letter);
                            $(`.letter-btn[data-index="${letterIndex}"]`).data('index', foundIndex)
                    .attr("data-index", foundIndex);
                    // $(".display_letter[data-index='" + letterIndex + "']").text(guessedLet);
                   

            //   $('.letter-btn[data-index="' + letterIndex + '"]').prop('disabled', true);
                    break; // Stop after finding the first match that is not excluded
                    }
                }
             }
             }

            }
            else{
                console.log(letterIndex);
            $(".display_letter[data-index='" + letterIndex + "']").text(guessedLet);
              $('.letter-btn[data-index="' + letterIndex + '"]').prop('disabled', true);
            }

  
                // Play correct guess sound
                const audiocorrect = document.getElementById('audiocorrect');
                audiocorrect.play();

                // Check for win
                if ($(".display_letter:contains('_')").length == 0) {

                
                //   if (!updatedDisplay.includes('_')) {
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
                $('#try-again').click(function () {
    location.reload();


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
                      if(cat_name == "Customized"){
                    $('.two-btn-group').css('display','block');}
                    else{
                        $('#try-again-button').css('display','block');
                    }
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


