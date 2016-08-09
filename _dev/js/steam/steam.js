!function($, window, document, _undefined) 
{
    /* /no minify */
    XenForo.SteamProfile = 
    {
        profileTemplate: '',

        init: function()
        {
            $(document).bind('XFAjaxSuccess', $.context(this, 'loadProfile'));
            $(document).on('QuickReplyComplete', $.context(this, 'createProfile'));
        },

        loadProfile: function(e)
        {
            if (XenForo.hasTemplateHtml(e.ajaxData))
            {
                var $templateHtml = $(e.ajaxData.templateHtml),
                    $profile = $templateHtml.find('.steamprofile');

                if ($profile.length)
                {
                    XenForo.SteamProfile.profileTemplate = SteamProfile.load($profile.attr('title'));
                }
            }
        },

        createProfile: function()
        {
            setTimeout(function() {
                $(document).find('.steamprofile').last().empty().append(XenForo.SteamProfile.profileTemplate);
            }, 1500);
        }
    }

    $(function()
    {
        XenForo.SteamProfile.init();
    });
}
(jQuery, this, document);