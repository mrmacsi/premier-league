<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="row p-3">
                    <div class="col-sm" v-if="allTeams && allTeams.length" v-for="(team, index) in allTeams">
                        {{ team.team_name }} strength : {{ team.strength }}
                    </div>
                </div>
                <div v-if="leagues && leagues.length" v-for="(league, index) in leagues">
                    <League :stats="league.stats" :matches="league.matches" :week="league.week" :estimations="league.estimations"/>
                </div>
                <a v-if="!disableButtons" class="btn m-2 btn-primary" role="button" v-on:click="playAll">Play All</a>
                <a v-if="!disableButtons" class="btn m-2 btn-primary pull-right float-right" role="button"
                   v-on:click="getNewWeek">Next
                    Week</a>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            leagues: [],
            week: 1,
            totalWeeks: 0,
            disableButtons: null,
            estimations: null,
            allTeams: null
        }
    },
    // Fetches posts when the component is created.
    created() {
        this.load();
    },
    methods: {
        getNewWeek() {
            if (this.totalWeeks >= this.week) {
                this.load()
            }
        },
        load() {
            var self = this;
            axios.get('match/week/' + this.week)
                .then(response => {
                    // JSON responses are automatically parsed.
                    self.leagues.push(response.data);
                    self.week++;
                    self.totalWeeks = response.data.totalWeeks;
                    self.allTeams = response.data.allTeams;
                    self.estimations = response.data.estimations;
                    console.log(self.estimations)
                    if (self.totalWeeks < self.week) {
                        self.disableButtons = true;
                    }
                })
                .catch(e => {
                    console.log(e)
                })
        },
        playAll() {
            var self = this;
            setTimeout(function(){
                if (self.totalWeeks >= self.week) {
                    self.getNewWeek();
                    self.playAll();
                }
            }, 1000);
        },
    }
}
</script>
