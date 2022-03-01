<template>
  <div class="input-fields">
    <p
        class="input-fields__name blackText"
    >
      {{data}}
    </p>
    <textarea
        :value="value"
        :placeholder="textInput"
        :type="type"
        class="input-fields__input whiteInputField"
        contenteditable="true"
        v-on="inputListeners"
        @focus="dateValue"
        @input="changeMask"
        id="field"
        ref="input"
    />
    <div :class="[select ?  '' : 'hide']" class="input-fields__list" id="list">
      <span v-for="item in list" @click="passValue(item)">
        {{ item }}
      </span>
    </div>
  </div>
</template>
<script>
export default  {
  name: 'input-fields',
  model: {
    prop: 'value',
    event: 'input'
  },
  props: {
    nameForm: String,
    textInput: String,
    dataName: Array,
    type: null,
  },
  data() {
    return {
      list: null,
      // name: ['Атбаш','Цезаря','Полибий','Тритемий'],
      result: null,
      select: false,
      value: '',
    }
  },
  mounted() {
    let input = document.getElementById('field')
    let list = document.getElementById('list')
    document.addEventListener('click', (elem) => {
      if (elem.target.id !== input.id && elem.target.id !== list.id && this.select === true) {
        this.select = false
      }
    })

  },

  computed: {
    inputListeners: function () {
      let vm = this
      return Object.assign({},
          this.$listeners,
          {
            input: function (event) {
              vm.$emit('input', vm.value)
            }
          }
      )
    },
  },
  methods: {
    dateValue () {
      if(this.type === "dataName") {
        this.list = this.dataName
        this.select = true
      }
    },
    changeMask(elem) {
      this.value = event.target.value
      if (this.type === "dataName") {
        let result = this.dataName.filter((item) => {
          return item.startsWith(this.value)
        })
        this.list = result
      }
    },
    passValue(item) {
      this.value = item
      this.$emit('input', this.value)
    },
  }
}
</script>
<style scoped lang="scss">

.whiteInputField {
  border: none;
  border-bottom: 1px solid rgba(253, 253, 253, 0.795) ;
  color: #ffffff;
  transition: 0.2s;
  &:hover, &:active, &:focus {
    border-bottom: 1px solid rgba(151, 151, 151, 0.9) ;
  }
}
.hide {
  display: none !important;
}
.whiteInputField::placeholder{
  color: rgba(255, 255, 255, 0.59);
}
.blackText {
  color: rgb(255, 255, 255);
}
.input-fields {
  margin-bottom: 45px;
  font-family: Raleway;
  font-style: normal;
  font-weight: normal;
  font-size: 14px;
  line-height: 16px;
  background: inherit;
  position: relative;

  @media (max-width: 700px) and (orientation: landscape) {
    width: 80vw;
    margin-bottom: 15px;
  }


  &__name {
    font-weight: 600;
    margin: 0 0 11px 0;
  }
  &__input {
    width: 100%;
    outline: none;
    background: inherit;
    padding:0;
    font-family: Raleway;
    font-style: normal;
    font-weight: normal;
    font-size: 14px;
    line-height: 12px;
    resize: none;
    overflow: auto;
    max-height: 60px;
  }

  &__list {
    position: absolute;
    bottom: -80px;
    display: flex;
    flex-direction: column;
    width: calc(100% + 10px);
    height: 83px;
    overflow-y: auto;
    background: white;
    border-radius: 4px;
    z-index: 1;
    cursor: pointer;
    overflow-x: hidden;
  }
}
</style>
