(function() {

  function apiFetch(url) {
    return fetch(url).then(response => {
      if (response.status >= 200 && response.status < 300) {
        return response.json();
      } else {
        return Promise.reject(new Error(response.status + ' ' + response.statusText));
      }
    }).then(json => {
      if (json.error) {
        return Promise.reject(new Error(json.error));
      } else {
        return Promise.resolve(json.data);
      }
    });
  }

  Vue.component('torrent-list', {
    props: ['torrents', 'error'],
    template: `
    <table class="table table-sm">
      <thead>
        <tr>
          <th>Name</th>
          <th>Size</th>
          <th>Downloaded</th>
          <th>Progress</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-show="error">
          <td class="text-danger" colspan="5">{{ error }}</td>
        </tr>
        <tr v-for="torrent in torrents">
          <td>{{ torrent.name }}</td>
          <td>{{ torrent.totalSize }}</td>
          <td>{{ torrent.haveValid }}</td>
          <td>{{ Math.floor(torrent.haveValid*10000/torrent.totalSize)/100 }} %</td>
          <td>{{ torrent.status }}</td>
        </tr>
      </tbody>
    </table>
    `
  });

  window.createTorrentList = function(el) {
    new Vue({
      el: el,
      template: '<torrent-list :torrents="torrents" :error="error"></torrent-list>',
      data: {
        torrents: [],
        error: null
      },
      methods: {
        update: function() {
          apiFetch('/api/list.php').then(data => {
            this.torrents = data;
          }).catch(error => {
            this.torrents = [];
            this.error = error.message;
          });
        }
      },
      mounted: function() { this.update(); }
    });
  }

})();
