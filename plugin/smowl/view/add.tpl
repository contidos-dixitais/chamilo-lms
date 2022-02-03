<div class="row">
    {% if global_tools|length or added_tools|length %}
        <div class="col-sm-3">
            {% if added_tools|length %}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h2 class="panel-title">{{ 'ToolsAdded'|get_plugin_lang('SmowlPlugin') }}</h2>
                    </div>
                    <ul class="list-group">
                        {% for tool in added_tools %}
                            <li class="list-group-item {{ type == tool.id ? 'active' : '' }}">
                                <div class="pull-right">
                                    <a href="{{ _p.web_plugin }}smowl/delete.php?id={{ tool.id }}&{{ _p.web_cid_query }}">
                                        {{ 'delete.png'|img(22, 'Delete'|get_lang) }}
                                    </a>
                                </div>
                                {{ tool.name|e }}
                            </li>
                        {% endfor %}
                    </ul>
                </div>
            {% endif %}

            {% if global_tools|length %}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h2 class="panel-title">{{ 'AvailableTools'|get_plugin_lang('SmowlPlugin') }}</h2>
                    </div>
                    <ul class="list-group">
                        {% for tool in global_tools %}
                            <li class="list-group-item {{ type == tool.id ? 'active' : '' }}">
                                <div class="pull-right">
                                    <a href="{{ _p.web_self }}?type={{ tool.id }}&{{ _p.web_cid_query }}">
                                        {{ 'add.png'|img(22, 'Add'|get_lang) }}
                                    </a>
                                    <a href="{{ _p.web_plugin }}ims_lti/delete.php?id={{ tool.id }}&{{ _p.web_cid_query }}">
                                        {{ 'settings.png'|img(22, 'Configure'|get_lang) }}
                                    </a>                                    
                                </div>
                                {{ tool.name|e }}
                            </li>
                        {% endfor %}
                    </ul>
                </div>
            {% endif %}
        </div>
    {% endif %}

    <div class="col-sm-9 {{ not global_tools|length and not added_tools|length ? 'col-md-offset-3' : '' }}">
        {{ form }}
    </div>
</div>
