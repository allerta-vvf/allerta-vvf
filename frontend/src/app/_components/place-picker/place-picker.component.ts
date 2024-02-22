import { Component, OnInit, Output, EventEmitter } from '@angular/core';
import { ToastrService } from 'ngx-toastr';
import { TranslateService } from '@ngx-translate/core';
import { ApiClientService } from 'src/app/_services/api-client.service';

interface Test {
  codice: string;
}

@Component({
  selector: 'place-picker',
  templateUrl: './place-picker.component.html',
  styleUrls: ['./place-picker.component.scss']
})
export class PlacePickerComponent implements OnInit {
  selectedRegion?: string;
  regions: string[] = [];

  selectedProvince?: string;
  selectedProvinceCodice?: string;
  provinces: string[] = [];

  selectedMunicipality?: string;
  selectedMunicipalityCodice?: string;
  municipalities: string[] = [];

  selectedAddress?: string;

  regionSelected = false;
  provinceSelected = false;
  municipalitySelected = false;
  addressSelected = false;

  @Output() addrSel = new EventEmitter<any>();

  constructor(private toastr: ToastrService, private api: ApiClientService, private translate: TranslateService) {
    this.api.get('places/italy/regions').then((res: any) => {
      this.regions = res;
      console.log(this.regions);
    }).catch((err: any) => {
      console.error(err);
      this.toastr.error(this.translate.instant("error_loading_regions"));
    });
  }

  ngOnInit() {
  }

  onRegionSelected() {
    this.selectedProvince = "";
    this.selectedMunicipality = "";
    this.selectedAddress = "";
    this.provinceSelected = false;
    this.municipalitySelected = false;

    this.api.get('places/italy/provinces/' + this.selectedRegion).then((res: any) => {
      this.provinces = res;
      console.log(this.provinces);
      this.regionSelected = true;
    }).catch((err: any) => {
      console.error(err);
      this.toastr.error(this.translate.instant("error_loading_provinces"));
    });
  }

  onProvinceSelected(event: any) {
    this.selectedMunicipality = "";
    this.selectedAddress = "";
    this.municipalitySelected = false;

    this.api.get('places/italy/municipalities/' + this.selectedProvince).then((res: any) => {
      this.municipalities = res;
      console.log(this.municipalities);

      this.selectedProvinceCodice = event.item.codice;
      this.provinceSelected = true;
    }).catch((err: any) => {
      console.error(err);
      this.toastr.error(this.translate.instant("error_loading_municipalities"));
    });
  }

  onMunicipalitySelected(event: any) {
    this.selectedAddress = "";

    this.selectedMunicipalityCodice = event.item.codice;

    this.municipalitySelected = true;
  }

  onAddressChanged() {
    this.addrSel.emit({
      region: this.selectedRegion,
      province: this.selectedProvinceCodice,
      municipality: this.selectedMunicipalityCodice,
      address: this.selectedAddress
    });
  }
}
