const addressData = require('china-area-data/v3/data')
import _ from 'lodash'

Vue.component('select-district',{
  props: {
    // 用来初始化省市区的值，在编辑时会用到
    initValue: {
      type: Array, // 格式是数组
      default: () => ([]), // 默认是个空数组
    }
  },
  data () {
    return {
      provinces: addressData['86'], // 省列表
      cities: {}, // 城市列表
      districts: {}, // 地区列表
      provinceId: '', // 当前选中的省
      cityId: '', // 当前选中的市
      districtId: '', // 当前选中的区
    }
  },
  watch: {
    provinceId (newVal) {
      if (!newVal) {
        this.city = ''
        this.cities = {}
        return
      }
      // 将城市列表设为当前省下的城市
      this.cities = addressData[newVal]
     
      // 如果当前选中的城市不在当前省下，则将选中城市清空
      if (!this.cities[this.cityId]) {
        this.cityId = ''
      }
    },
    cityId (newVal){
    
      if(!newVal){
        this.districtId = ''
        this.districts = {}
        return
      }
      this.districts = addressData[newVal]
      if(!this.districts[this.districtId]){
        this.districtId = ''
      }
    },
    districtId (){
      this.$emit('change',[this.provinces[this.provinceId], this.cities[this.cityId], this.districts[this.districtId]]);
    },
  },
  created () {
    this.setFromValue(this.initValue);
  },
  methods: {
    setFromValue (value) {
      value = _.filter(value)
      if(value.length === 0){
        this.provinceId = ''
      }
      const provinceId = _.findKey(this.provinces,(o) => {
        return o === value[0]
      })
      if(!provinceId){
        this.provinceId = ''
        return
      }
      this.provinceId = provinceId
      const cityId = _.findKey(addressData[provinceId], o => o === value[1]);
      // 没找到，清空城市的值
      if (!cityId) {
        this.cityId = '';
        return;
      }
      // 将当前城市设置成对应的ID
      this.cityId = cityId;
      const districtId = _.findKey(addressData[cityId], o => o === value[2])
      if (!districtId){
        this.districtId = ''
        return
      }
      this.districtId = districtId
    }
  }
})