import { Component, OnInit, Input } from '@angular/core';
import 'chartjs-plugin-colorschemes-v3/src/plugins/plugin.colorschemes';
import { Aspect6 } from 'chartjs-plugin-colorschemes-v3/src/colorschemes/colorschemes.office';

@Component({
  selector: 'chart',
  templateUrl: './chart.component.html',
  styleUrls: ['./chart.component.scss']
})
export class ChartComponent implements OnInit {
  @Input() type = "";
  @Input() data: any = {};

  options: any = {};

  constructor() { }

  ngOnInit(): void {
    const documentStyle = getComputedStyle(document.documentElement);
    const textColor = documentStyle.getPropertyValue('--text-color');

    this.options = {
      responsive: true,
      maintainAspectRatio: false,
      legend: {
        position: 'bottom'
      },
      scales: { },
      plugins: {
        legend: {
          labels: {
            usePointStyle: true,
            color: textColor,
            generateLabels: (chart: any) => {
              const datasets = chart.data.datasets;
              if (!datasets.length) {
                return [];
              }
              return datasets[0].data.map((data: any, i: number) => ({
                text: `${chart.data.labels[i]} (${data})`,
                fillStyle: datasets[0].backgroundColor[i],
                index: i
              }))
            }
          }
        },
        colorschemes: {
          scheme: Aspect6
        }
      },
      animation: {
        duration: 1000
      }
    };

    if (this.type === "bar") {
      const textColorSecondary = documentStyle.getPropertyValue('--text-color-secondary');
      const surfaceBorder = documentStyle.getPropertyValue('--surface-border');
      this.options.scales = {
        y: {
          beginAtZero: true,
          ticks: {
            color: textColorSecondary
          },
          grid: {
            color: surfaceBorder,
            drawBorder: false
          }
        },
        x: {
          ticks: {
            color: textColorSecondary
          },
          grid: {
            color: surfaceBorder,
            drawBorder: false
          }
        }
      };
    } else if (this.type === "horizontal-bar") {
      this.options.indexAxis = 'y';
      this.type = "bar";
    } else if (this.type === "doughnut") {
      this.options.cutout = '60%';
    }
  }

}
