jQuery(document).ready(function($) {
    $('#dn-fetch-news').on('click', function() {
        $('#dn-loader').show(); // Show loader
        $('#dn-fetch-news').prop('disabled', true); // Disable button to prevent multiple clicks

        $.ajax({
            url: dn_news.ajax_url,
            type: 'POST',
            data: {
                action: 'fetch_gnews_articles',
                _nonce: $('#dn_gnews_nonce').val(),
            },
            success: function(response) {
                $('#dn-loader').hide(); // Hide loader
                $('#dn-fetch-news').prop('disabled', false); // Enable button

                if (response.success) {
                    $('#dn-news-table').show(); // Show the news table
                    let newsHTML = '';

                    // Populate the table with news articles
                    $.each(response.data, function(index, article) {
                        newsHTML += `
                            <tr>
                                <td>${article.title}</td>
                                <td>${article.content}</td>
                                <td>${article.publishedAt}</td>
                                <td><a href="${article.link}" target="_blank">View</a></td>
                            </tr>
                        `;
                    });

                    $('#dn-news-table-body').html(newsHTML);

                    // Show success alert
                    alert('News fetched successfully!');
                } else {
                    alert('Failed to fetch news: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                $('#dn-loader').hide(); // Hide loader
                $('#dn-fetch-news').prop('disabled', false); // Enable button
                alert('An error occurred: ' + error);
            }
        });
    });
});
