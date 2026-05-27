


@extends('en.layouts.master')
@section('content')
<style>
#Top_bar {
    background-color: rgb(36 37 42);
}
.jumbotron{
  background-color: white !important;
  padding: 94px;

}
.check {
  padding: 11px;
    display: block;
    border: 1px solid green;
    width: 52px;
    margin: auto;
    margin-top: 74px;
    border-radius: 51%;
    height: 52px;
    color: white;
    background-color: #158c0d;
    font-size: 28px;
    font-weight: 700;
}
</style>

    <div class="jumbotron text-center">
    <i style="color: green;font-size: 81px;margin-left: 19px;"class="fas fa-check-circle"></i>
    <h1 class="display-3">Thank You!</h1>
    <p class="lead"style="color: #158c0d;"><strong>Your Answers have been received, Please Check your Email</strong></p>
    <hr>


  </div>

  @endsection

