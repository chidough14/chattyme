<template>
    <div class="container-chat clearfix">
        <notifications group="foo" :classes="notificationClass"/>
        <div class="people-list" id="people-list">
            <div class="search">
                <input type="text" placeholder="search" />
                <i class="fa fa-search"></i>
            </div>
            <ul class="list">
                <li class="clearfix" v-for="(user, index) in friends" :key="index" @click="selectUser(user.id)">
                    <div class="about">
                        <div class="name">{{ user.name }}</div>
                        <div class="status">
                            <i class="fa fa-circle online" ></i> online
                            <!-- <div v-if="onlineUser(user.id)"><i class="fa fa-circle online" ></i> online</div>
                            <div v-else><i class="fa fa-circle offline" ></i> offline</div> -->
                        </div>
                    </div>
                </li>
            </ul>
        </div>

        <div class="chat">
            <div class="chat-header clearfix">
                <img src="/img/avatar2.jpg" alt="avatar" style="width: 80px; height: 80px;"/>

                <div class="chat-about">
                    <div v-if="userMessage.user" class="chat-with">{{ userMessage.user.name }}</div>
                </div>
                <i class="fa fa-star"></i>
                <ul class="nav nav-tabs">
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdow-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">...</a>
                        <div class="dropdown-menu">
                            <a href="#" class="dropdown-item" @click.prevent="deleteAllMessages()">Delete All Message</a>
                        </div>
                    </li>
                </ul>
            </div> <!-- end chat-header -->

            <div class="chat-history">
                <ul style="height: 500px; overflow-y: scroll;" v-chat-scroll>
                    <li class="clearfix" v-for="(message, index) in userMessage.messages" :key="index">
                        <div class="message-data align-right">
                            <span class="message-data-time" >{{ message.created_at| timeFormat }}</span> &nbsp; &nbsp;
                            <span class="message-data-name" >{{ message.user.name }}</span> <i class="fa fa-circle me"></i>
                            <ul class="nav nav-tabs">
                                <li class="nav-item dropdown">
                                    <a href="#" class="nav-link dropdow-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">...</a>
                                    <div class="dropdown-menu">
                                        <a href="#" @click.prevent="deleteSingleMessage(message.id)" class="dropdown-item">Delete Message</a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div v-if="message.message" :class="`message float-right ${message.user.id == userMessage.user.id ? 'other-message' : 'my-message'}`">
                            {{ message.message }}
                        </div>

                        <div v-if="message.image">
                            <img :src="'/storage/'+message.image" style="width: 100px; height: auto;">
                        </div>
                    </li>
                </ul>

            </div> <!-- end chat-history -->

            <div class="chat-message clearfix">
                <picker v-if="emostatus" set="emojione" @select="onInput" title="Pick your emoji" />
                <p v-if="typing">{{ typing }} is typing</p>

                <div v-if="userMessage.user">
                    <textarea @keydown="typingEvent(userMessage.user.id)" @keydown.enter="sendMessage" v-model="message" name="message-to-send" id="message-to-send" placeholder ="Type your message" rows="3"></textarea>
                    <i class="fa fa-file-o" @click="toggleEmo"></i> &nbsp;&nbsp;&nbsp;

                    <file-upload
                        :post-action='"/sendimage/"+ userMessage.user.id'
                        ref="upload"
                        @input-file="upload"
                        :headers="{'X-CSRF-TOKEN': token}"><i class="fa fa-file-image-o"></i>
                    </file-upload>
                </div>

                <div  v-else>
                    <textarea disabled name="message-to-send" id="message-to-send" placeholder ="Type your message" rows="3"></textarea>
                </div>
                <button>Send</button>

            </div>








            </div> <!-- end chat-message -->

        </div>
</template>

<script>
import _ from 'lodash'
import { Picker } from 'emoji-mart-vue'

export default {
    components: {
        Picker
    },
    mounted() {
        this.test()

        window.Echo.join('liveuser')
        .here((users) => {
            console.log('online users', users)
            this.onlinefriends = users
        })
        .joining((user)=> {
            //console.log('joining', user.name)
            this.notificationClass = 'vue-notification success'
            this.$notify({
                group: 'foo',
                text: user.name + 'just joined the room'
            })
            this.onlinefriends.push(user)
        })
        .leaving((user)=> {
            //console.log('leaving', user.name)
            this.notificationClass = 'vue-notification error'
            this.$notify({
                group: 'foo',
                text: user.name + 'just left the room'
            })
            this.onlinefriends.splice(this.onlinefriends.indexOf(user), 1)
        })


        window.Echo.private(`chat.${authuser.id}`)
        .listen('.App\\Events\\MessageSent', (e)=> {
            this.selectUser(e.message.from)
            this.playSound('http://soundbible.com/mp3/sms-alert-5-daniel_simon.mp3')
            //console.log(e);
        })


        Echo.private('typingevent')
        .listenForWhisper('typing', (e)=> {
            if(e.user.id == this.userMessage.user.id && e.userId == authuser.id) {
                this.typing = e.user.name

                setTimeout(()=> {
                    this.typing = ''
                }, 2000)
            }
        })

        this.$store.dispatch('userList')
    },
    data() {
        return {
            newuserlist: [],
            message: '',
            typing: '',
            onlinefriends: [],
            notificationClass: null,
            emostatus: false,
            token: document.head.querySelector('meta[name= "csrf-token"]').content,
        }
    },
    computed: {
        userList() {
            return this.$store.getters.userList
        },
        userMessage(){
            return this.$store.getters.userMessage
        },
        friends(){
            return this.onlinefriends.filter((user) => {
               return  user.id != authuser.id
            })
        }
    },
    methods: {
        upload() {
            this.$refs.upload.active = true

            this.selectUser(this.userMessage.user.id)
        },
        selectUser(id) {
            this.$store.dispatch('userMessage', id)
        },
        sendMessage(e) {
            e.preventDefault()
            if (this.message != '') {
                axios.post('/sendmessage', {
                    message: this.message,
                    user_id: this.userMessage.user.id
                })
                .then(res => {
                    this.message = ''
                    this.selectUser(this.userMessage.user.id)
                })
                .catch(err => console.log(err))
            }
        },
        deleteSingleMessage(messageId) {
            axios.get ('/deletemessage/'+ messageId)
            .then(res => {
                this.selectUser(this.userMessage.user.id)
            })
            .catch(err => console.log(err))
        },
        deleteAllMessages() {
            axios.get ('deleteallmessages/'+ this.userMessage.user.id)
            .then(res => {
                this.selectUser(this.userMessage.user.id)
            })
            .catch(err => console.log(err))
        },
        typingEvent(userId) {
            Echo.private('typingevent').whisper('typing', {
                'user': authuser,
                'typing': this.message,
                'userId': userId
            })
        },
        onlineUser(userId) {
            return _.find(this.onlinefriends, {'id': userId})
        },
        playSound (sound) {
            if(sound) {
                var audio = new Audio(sound);
                audio.play();
            }
        },
        toggleEmo(){
            this.emostatus = !this.emostatus
        },
        onInput(e){
           if(!e){
               return false;
           }
           if(!this.message){
              this.message = e.native
           } else {
               this.message = this.message + e.native
           }
           //console.log(e)
        },
        test(){
            axios.get ('userlist')
            .then(res => {
                console.log(res);
                this.newuserlist = res.data
            })
            .catch(err => console.log(err))
        }
    },
}
</script>

<style>
    .people-list ul {
        overflow-y: scroll ! important;
    }
</style>
