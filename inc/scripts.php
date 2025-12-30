<script src="scripts/jquery-1.12.4.min.js"></script>
      <script src="scripts/bootstrap-3.3.7.min.js"></script>
      <script>
         $(".nav .dropdown").hover(function() {
           $(this).find(".dropdown-toggle").dropdown("toggle");
         });
         $(document).ready(function(){
  $("#busca").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#resultados *").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});
      </script>