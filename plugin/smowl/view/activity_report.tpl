{%  if tool.description %}
<p class="lead">{{ tool.description|e|nl2br }}</p>
{%  endif %}

<form id="resultsForm" target="smowl" action="https://results.smowltech.net/resultsControllerLMS.php" method="POST">
    <input type="hidden" name="Entity" value="{{ entity_name }}" />
    <input type="hidden" name="swlLicenseKey" value="{{ license_key }}" />
    <input type="hidden" name="Modality" value="quiz" />
    <input type="hidden" name="idCourse" value="{{ courseCode }}" />
    <input type="hidden" name="anombres" value="{{ users }}" />
    <input type="hidden" name="lang" value="{{ lang }}" />
    <input type="hidden" name="course_MoodleName" value="{{ tool.name }}" />
    <input type="hidden" name="type" value="2" />
</form>
<iframe id="smowl" name="smowl" width="100%" height="3375" src="#" frameborder="0" allowfullscreen></iframe>
<script>
    var resultsForm = document.getElementById("resultsForm");
    resultsForm.style.display = "none";
    resultsForm.submit();
</script>