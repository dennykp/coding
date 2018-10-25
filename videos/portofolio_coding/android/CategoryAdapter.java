package harus.bisa;

import harus.bisa.Choice;
import android.app.Activity;
import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.ImageView;
import android.widget.TextView;

	public class CategoryAdapter extends ArrayAdapter<Choice> {
		
		Context context;
		int layoutResourceId;
		Choice data_array[];
		
			public CategoryAdapter(Context context, int layoutResourceId, Choice[] data) {
		        super(context, layoutResourceId, data);
		        this.layoutResourceId = layoutResourceId;
		        this.context = context;
		        this.data_array = data;
		
		    }
			    public View getView(int position, View convertView, ViewGroup parent) {
			        View row = convertView;
			        WeatherHolder holder = null;
			        if(row == null)
			        {
			        	LayoutInflater inflater = ((Activity)context).getLayoutInflater();
			            row = inflater.inflate(layoutResourceId, parent, false); 
			            holder = new WeatherHolder();
			            holder.imgIcon = (ImageView)row.findViewById(R.id.imgIcon);
			            holder.txt_isi_list_awal = (TextView)row.findViewById(R.id.txt_isi_list_awal);
			            row.setTag(holder);
			           
			        }
			        else
			        {
			            holder = (WeatherHolder)row.getTag();
			        }
			        Choice weather = data_array[position];
			        holder.txt_isi_list_awal.setText(weather.title);
			        holder.imgIcon.setImageResource(weather.icon);
			        return row;
		    }
			    
			static class WeatherHolder
			    {
			        ImageView imgIcon;
			        TextView txt_isi_list_awal;
			    }
			}
