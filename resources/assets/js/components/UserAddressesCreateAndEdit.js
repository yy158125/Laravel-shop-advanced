Vue.component('user-addresses-create-edit',{
  data() {
    return {
      province: '', // 省
      city: '', // 市
      district: '', // 区
    }
  },
  methods: {
    onDistrictChanged(val) {
      if(val.length === 3){
        this.province = val[0]
        this.city = val[1]
        this.district = val[2]
      }
    }
  }
})