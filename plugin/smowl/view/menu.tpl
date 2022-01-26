{%  if tool.description %}
<p class="lead">{{ tool.description|e|nl2br }}</p>
{%  endif %}
<div class="row">
    <div class="col-md-6">
        <div class="thumbnail">
            <a href="{{ register_user_url }}">
                <img src="resources/img/128/register.png">
            </a>
            <div class="caption">
                <p class="text-center">
                    <a class="btn btn-default btn-sm"
                       href="{{ register_user_url }}">Registrarse para acceder al examen</a>
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="thumbnail">
            <a href="{{ exam_url }}">
                <img src="resources/img/128/exam.png">
            </a>
            <div class="caption">
                <p class="text-center">
                    <a class="btn btn-default btn-sm"
                       href="{{ exam_url }}">Acceder al examen</a>
                </p>
            </div>
        </div>
    </div>
</div>
{% if _u.is_admin %}
    <div class="row">
        <div class="col-md-6">
            <div class="thumbnail">
                <a href="{{ user_reports_url }}">
                    <img src="resources/img/128/user-reports.png">
                </a>
                <div class="caption">
                    <p class="text-center">
                        <a class="btn btn-default btn-sm"
                        href="{{ user_reports_url }}">Informe de registro de usuarios</a>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="thumbnail">
                <a href="{{ activity_reports_url }}">
                    <img src="resources/img/128/activity-reports.png">
                </a>
                <div class="caption">
                    <p class="text-center">
                        <a class="btn btn-default btn-sm"
                        href="{{ activity_reports_url }}">Informe de actividad</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
{% endif %}

