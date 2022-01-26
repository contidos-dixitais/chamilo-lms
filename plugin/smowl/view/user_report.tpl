{%  if tool.description %}
<p class="lead">{{ tool.description|e|nl2br }}</p>
{%  endif %}

<form id="registerForm" target="smowl" action="https://results.smowltech.net/registerControllerLMS.php" method="POST">
    <input type="hidden" name="entity_Name" value="{{ entity_name }}" />
    <input type="hidden" name="swlLicenseKey" value="{{ license_key }}" />
    <input type="hidden" name="anombres" value="{{ users }}" />
    <input type="hidden" name="lang" value="{{ lang }}" />
    <input type="hidden" name="type" value="2" />
</form>
<iframe id="smowl" name="smowl" width="100%" height="3375" src="#" frameborder="0" allowfullscreen></iframe>
<script>
    var registerForm = document.getElementById("registerForm");
    registerForm.style.display = "none";
    registerForm.submit();
</script>