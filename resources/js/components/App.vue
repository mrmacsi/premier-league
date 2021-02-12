<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div v-if="leagues && leagues.length" v-for="(league, index) in leagues">
                    <League :stats="league.stats" :matches="league.matches" :week="league.week" />
                </div>
                <a class="btn m-2 btn-primary" role="button">Play All</a>
                <a class="btn m-2 btn-primary pull-right float-right" role="button" v-on:click="getNewWeek">Next Week</a>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            leagues: []
        }
    },
    // Fetches posts when the component is created.
    created() {
        this.getNewWeek();
    },
    methods: {
        getNewWeek(){
            var self = this;
            axios.get('match/week')
                .then(response => {
                    // JSON responses are automatically parsed.
                    self.leagues.push(response.data);
                })
                .catch(e => {
                    console.log(e)
                })
        }
    }
}
</script>
