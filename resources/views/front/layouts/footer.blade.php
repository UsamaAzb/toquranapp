<footer id="Footer" class="clearfix">
    <div class="widgets_wrapper">
        <div class="container">
            <div class="column one-fourth">
                <aside class="widget_text widget widget_custom_html">
                    <div class="textwidget custom-html-widget">
                        <p class="big" style="color:#fff">
                            COURSES & PRICING
                        </p>
                        <hr class="no_line" style="margin:0 auto 20px">
                        <ul>
                            <li>
                              <a href="{{url('en/kids-courses')}}"> Kids Courses</a>
                            </li>
                            <li>
                              <a href="{{url('en/adult-courses')}}"> Adults Courses</a>
                            </li>
                            <li>
                              <a href="{{url('en/int-school')}}"> International School</a>
                            </li>
                            <li>
                              <a href="{{url('en/adult-courses')}}"> Business English</a>
                            </li>
                            <li>
                              <a href="{{url('en/adult-courses')}}"> Advanced Conversation</a>
                            </li>
                          </ul>
                    </div>
                </aside>
            </div>
            <div class="column one-fourth">
                <aside class="widget_text widget widget_custom_html">
                    <div class="textwidget custom-html-widget">
                        <p class="big" style="color:#fff">
                            EXPLORE OUR SITE
                        </p>
                        <hr class="no_line" style="margin:0 auto 20px">
                        <ul>
                            <li>
                              <a href="{{url('en/aboutus')}}"> About us</a>
                            </li>
                            <li>
                              <a href="{{url('en/login')}}"> LMS</a>
                            </li>
                            <li>
                              <a href="{{url('en/placement-test/register')}}"> Placement Test</a>
                            </li>
                            <li>
                              <a href="{{url('en/fees')}}"> Fees</a>
                            </li>
                        </ul>
                    </div>
                </aside>
            </div>
            <div class="column one-fourth">
                <aside class="widget_text widget widget_custom_html">
                    <div class="textwidget custom-html-widget">
                        <p class="big" style="color:#fff">
                            CONTACT US
                        </p>
                        <hr class="no_line" style="margin:0 auto 20px">
                        <p>
                            If you have any questions,
                            <br> please contact us at <a target="_blank" href="mailto:support@ibeducation.org"><span >support@teacherusama.com</span></a>
                        </p>
                        <h4 class="themecolor">
                        <a target="_blank" href="tel:+201091051913">+201091051913</a>
                        </h4>
                    </div>
                </aside>
            </div>
        </div>
    </div>
    <div class="footer_copy">
        <div class="container">
            <div class="column one">
                <div class="copyright">
                  Copyright &copy; 2015 by <a target="_blank" rel="nofollow" href="https://webdative.com">Webdative</a>. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</footer>
        <!-- JS -->
<script src="{{asset('public/js/jquery-2.1.4.min.js')}}"></script>
<!--<script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>-->
<script src="{{asset('public/plugins/bootstrap5-2/js/bootstrap.bundle.min.js')}}"></script>

<!--menu in mobile-->
<script src="{{asset('public/js/mfn.menu.js')}}" defer></script>

<script src="{{asset('public/js/jquery.plugins.js')}}"></script>
<!--<script src="{{asset('public/js/jquery.jplayer.min.js')}}"></script>-->
<script src="{{asset('public/js/animations/animations.js')}}"></script>
<script src="{{asset('public/js/translate3d.js')}}"></script>
<script src="{{asset('public/js/scripts.js')}}" defer></script>
<script src="{{asset('assets/admin/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js')}}"></script>
<!-- AdminLTE App -->
<script src="{{asset('assets/admin/dist/js/adminlte.js')}}"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<!-- <script src="{{asset('assets/admin/dist/js/pages/dashboard.js')}}"></script> -->
<!-- AdminLTE for demo purposes -->
<script src="{{asset('assets/admin/dist/js/demo.js')}}"></script>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>

<script src="{{asset('assets/admin/tinymce/js/tinymce/tinymce.min.js')}}" ></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.4.0/dropzone.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery.lazy/1.7.9/jquery.lazy.min.js"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery.lazy/1.7.9/jquery.lazy.plugins.min.js"></script>
    
    <script src="{{asset('assets/front/js/recorder.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/js/fontawesome.min.js" integrity="sha512-j3gF1rYV2kvAKJ0Jo5CdgLgSYS7QYmBVVUjduXdoeBkc4NFV4aSRTi+Rodkiy9ht7ZYEwF+s09S43Z1Y+ujUkA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

         <script>
$(function() {
        $('.lazy').Lazy();
    });
    </script>



        </div>
        <div id="Side_slide" class="right dark" data-width="250">
            <div class="close-wrapper">
                <a href="#" class="close"><i class="icon-cancel-fine"></i></a>
            </div>
            <!--<div class="extras">-->
            <!--  <a href="" class="action_button" target="_blank">Log in</a>-->
            <!--    <div class="extras-wrapper"></div>-->
            <!--</div>-->
            <div class="menu_wrapper"></div>
        </div>
        <div id="body_overlay"></div>
@stack("show_video")

@stack('record')
@stack('timer')
@stack('audio_play')
@stack("show_foundation_video")

<script>
        // Disable right-click on the entire page
        // document.addEventListener('contextmenu', function (e) {
        //     e.preventDefault();
        // });

    //     $('#iframe').load(function(){
    //         console.log('d');
    // $('#iframe').contents().find("#toolbar #end").hide();
// });
    </script>
<script>

function bootstrapTabControl(){
  var i, items = $('.nav-link'), pane = $('.tab-pane');

  $('.nav-item').on('click', function(){
      $('.sumbit-btn').css('display','none');
      $('.nexttab').prop('disabled', false);
      });
      $('#custom-tabs-one-tab li:last').on('click', function(){
        $('.sumbit-btn').css('display','block');
        $('.nexttab').prop('disabled', true);

      });
  // next
  $('.nexttab').on('click', function(){

      for(i = 0; i < items.length; i++){
          if($(items[i]).hasClass('active') == true){
            if(items.length -2 == i ){
              $('.sumbit-btn').css('display','block');
              $('.nexttab').prop('disabled', true);
            }
            else{
              $('.sumbit-btn').css('display','none');
              $('.nexttab').prop('disabled', false);

            }
              break;
          }


      }
      if(i < items.length - 1){
 document.body.scrollTop = 0; // For Safari
  document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
          // for tab
          $(items[i]).removeClass('active');
          $(items[i+1]).addClass('active');
          // for pane
          $(pane[i]).removeClass('show active');
          $(pane[i+1]).addClass('show active');

      }

  });
  // Prev
  $('.prevtab').on('click', function(){
    $('.sumbit-btn').css('display','none');
    $('.nexttab').prop('disabled', false);

      for(i = 0; i < items.length; i++){
          if($(items[i]).hasClass('active') == true){
              break;
          }
      }
      if(i != 0){
          document.body.scrollTop = 0; // For Safari
  document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
          // for tab
          $(items[i]).removeClass('active');
          $(items[i-1]).addClass('active');
          // for pane
          $(pane[i]).removeClass('show active');
          $(pane[i-1]).addClass('show active');
      }
  });
}
bootstrapTabControl();

</script>



<script type="text/javascript">
var useDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;

tinymce.init({
  selector: 'textarea.tinymce-editor',
  plugins: 'image code print preview paste importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern noneditable help charmap quickbars emoticons',
  imagetools_cors_hosts: ['picsum.photos'],
  menubar: 'file edit view insert format tools table help',
  toolbar: 'image code |undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl',
  toolbar_sticky: true,
  autosave_ask_before_unload: true,
  autosave_interval: '30s',
  autosave_prefix: '{path}{query}-{id}-',
  autosave_restore_when_empty: false,
  autosave_retention: '2m',
  image_advtab: true,



  file_picker_types: 'image',
     images_upload_handler: function (blobInfo, success, failure) {
         let data = new FormData();
         data.append('file', blobInfo.blob(), blobInfo.filename());
         axios.post('/admin/upload/images', data)
             .then(function (res) {
                 success(res.data.location);
             })
             .catch(function (err) {
                 failure('HTTP Error: ' + err.message);
             });
     }
,





  link_list: [
    { title: 'My page 1', value: 'http://www.tinymce.com' },
    { title: 'My page 2', value: 'http://www.moxiecode.com' }
  ],
  image_list: [
    { title: 'My page 1', value: 'http://www.tinymce.com' },
    { title: 'My page 2', value: 'http://www.moxiecode.com' }
  ],
  image_class_list: [
    { title: 'None', value: '' },
    { title: 'Some class', value: 'class-name' }
  ],
  importcss_append: true,
  file_picker_callback: function (callback, value, meta) {
    /* Provide file and text for the link dialog */
    if (meta.filetype === 'file') {
      callback('https://www.google.com/logos/google.jpg', { text: 'My text' });
    }

    /* Provide image and alt text for the image dialog */
    if (meta.filetype === 'image') {
      callback('https://www.google.com/logos/google.jpg', { alt: 'My alt text' });
    }

    /* Provide alternative source and posted for the media dialog */
    if (meta.filetype === 'media') {
      callback('movie.mp4', { source2: 'alt.ogg', poster: 'https://www.google.com/logos/google.jpg' });
    }
  },
  templates: [
        { title: 'New Table', description: 'creates a new table', content: '<div class="mceTmpl"><table width="98%%"  border="0" cellspacing="0" cellpadding="0"><tr><th scope="col"> </th><th scope="col"> </th></tr><tr><td> </td><td> </td></tr></table></div>' },
    { title: 'Starting my story', description: 'A cure for writers block', content: 'Once upon a time...' },
    { title: 'New list with dates', description: 'New List with dates', content: '<div class="mceTmpl"><span class="cdate">cdate</span><br /><span class="mdate">mdate</span><h2>My List</h2><ul><li></li><li></li></ul></div>' }
  ],
  template_cdate_format: '[Date Created (CDATE): %m/%d/%Y : %H:%M:%S]',
  template_mdate_format: '[Date Modified (MDATE): %m/%d/%Y : %H:%M:%S]',
  height: 300,
  image_caption: true,
  quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
  noneditable_noneditable_class: 'mceNonEditable',
  toolbar_mode: 'sliding',
  contextmenu: 'link image imagetools table',
  skin: useDarkMode ? 'oxide-dark' : 'oxide',
  content_css: useDarkMode ? 'dark' : 'default',
  content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
 });
 </script>

