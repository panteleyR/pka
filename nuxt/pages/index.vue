<template>
  <div class="container">
    <div class="list">
      <h1 class="list__title">
        Шифры
      </h1>
      <div class="list__filter" >
        <input-fields
          name-form="Шифр"
          text-input="Выберете шифр"
          :data-name="nameList"
          type="dataName"
          @input="filter"
          v-model="name"
        />
        <div class="list__search">
          <input type="text" v-model="data" placeholder="Введите слово" >
        </div>
        <div class="list__search" v-if="this.isNeedKey">
          <input type="text" v-model="key" placeholder="Введите ключ" >
        </div>
        <button v-on:click="search" class="list__btn">Шифровать</button>
      </div>
      <div class="list__links" v-html="answer">
      </div>
    </div>
  </div>
</template>

<script>
import InputFields from "../components/inputFields.vue";
export default {
  components: {
    InputFields
  },
  data() {
    return {
      name: '',
      data: '',
      key: '',
      answer: "",
      crypt: [],
      nameList: {},
      isNeedKey: false
    }
  },
  mounted () {
    this.$axios.$get(`/api/crypt/`).then((res) => {
      this.crypt = res
      this.nameList = this.crypt.map((item)=> {
        return item.name
      })
    })
  },
  methods: {
    filter(i) {
      let item = this.crypt.find((item)=> {
        return item.name === i
      })
      this.isNeedKey = item.key
    },
    search() {
      let item = this.crypt.find((item)=> {
        return item.name === this.name
      })
      let url = `/api/crypt/${item.code}?inputText=${this.data}`
      if (item.key === true) {
        url = url + `&key=${this.key}`
      }
      this.$axios.$get(url).then((res) => {
        this.answer = res
      })
    }
  }
}
</script>

<style scoped lang="sass">
.container
  margin: 0 auto
  display: flex
  justify-content: center
  align-items: center
  text-align: center
  background: rgb(32, 35, 41)
  max-width: 1440px
  font-family: Arial
  min-height: 100vh
.list
  &__title
    display: block
    font-weight: 300
    font-size: 100px
    letter-spacing: 1px
    color: whitesmoke
    @media(min-width: 370px) and (max-width: 600px)
      font-size: 27px
    @media(max-width: 369px)
      font-size: 35px

  &__subtitle
    font-weight: 300
    font-size: 42px
    word-spacing: 5px
    padding-bottom: 15px

  &__links
    padding-top: 35px
    color: white
    max-width: 80vw
    overflow: hidden
    font-size: 21px
    word-wrap: break-word



  &__filter
    display: flex
    flex-direction: column
    justify-content: space-around
    max-width: 900px
    margin: 10px auto
    @media(max-width: 450px)
      flex-direction: column
      width: 80%
      margin: 10px auto


  &__search
    cursor: pointer
    border: 2px solid whitesmoke
    margin: 10px 0
    padding: 10px
    background: whitesmoke

  &__selected
    cursor: pointer
    border: 2px solid whitesmoke
    margin: 10px 0
    padding: 10px
    background: whitesmoke

  &__selected-text
    color: grey

  &__btn
    margin: 10px 0
    padding: 15px 10px
    background: #3b3e43
    color: whitesmoke
    font-size: 16px
    font-weight: bold
    border-radius: 7px
    @media(max-width: 450px)
      width: 140px
      height: 50px

</style>
