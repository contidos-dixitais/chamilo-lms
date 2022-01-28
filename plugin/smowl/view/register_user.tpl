{%  if tool.description %}
    <p class="lead">{{ tool.description|e|nl2br }}</p>
{%  endif %}

<p>Para acceder a la prueba proctorizada deberá completar el registro en SMOWL pulsando sobre <a href="https://swl.smowltech.net/monitor/controllerReg.php?entity_Name={{ entity_name }}&swlLicenseKey={{ license_key }}&user_idUser={{ user_id }}&lang={{ lang }}&course_Container={{ session_id }}&Course_link={{ course_link }}"
 target="_blank">este enlace</a>.</p>
 
<p>Si ya ha completado anteriormente el proceso de registro y sigue sin poder acceder al examen es posible que este pendiente de una validación manual por la plataforma SMOWL®.</p>
